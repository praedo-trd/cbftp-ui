<?php

namespace TRD\Handler;

use TRD\Processor\ProcessorResponse;
use TRD\Model\Sites;
use TRD\Model\Settings;
use TRD\Utility\AnnounceString;
use TRD\Utility\ConsoleDebug;
use TRD\Utility\ReleaseName;
use TRD\Utility\CBFTP;

class NewRaceHandler extends \TRD\Handler\Handler
{
    public function handle(ProcessorResponse $response)
    {
        if (!isset($response->data['src'])) {
            return $response;
        }

        $db = $this->container['db'];
        $sites = $this->container['models']['sites'];
        $skiplists = $this->container['models']['skiplists'];
        $settings = $this->container['models']['settings'];

        $str = $response->data['msg'];

        $section = null;
        $rlsname = null;

        $siteName = $response->data['src'];
        $siteInfo = $sites->getSite($siteName);

        // get irc strings
        $irc = $siteInfo->irc->strings;

        $extraction = \TRD\Utility\IRCExtractor::extract($siteInfo->irc->strings, array('newstring', 'prestring'), $str);
        $section = $extraction['section'];
        $rlsname = $extraction['rlsname'];

        // we have a match
        $rlsname = ReleaseName::getReleaseNameFromDirectory($rlsname);
        if (preg_match($settings->get('baddir'), $rlsname)) {
            $rlsname = null;
            $this->container['log']->debug(sprintf(
                'Site %s found rlsname %s in section %s which matched the baddir setting',
                $response->data['src'],
                $section,
                $rlsname
            ));
        }

        if ($section !== null and $rlsname !== null) {
            $this->container['log']->debug(sprintf(
                'Site %s found new race string for section %s with rlsname %s',
                $response->data['src'],
                $section,
                $rlsname
            ));

            // add a new section if we come across it
            $sites->addSection($response->data['src'], $section);

            // check pretime
            $spacedRelease = ReleaseName::spacify($rlsname);
            $pretime = $db->fetchAssoc("SELECT * FROM pre WHERE rlsname = ?", array($spacedRelease));

            if ($settings->get('require_pretime') == true and empty($pretime)) {
                $this->container['log']->debug(sprintf(
                    'Site %s didn\'t start race %s because no pre time',
                    $response->data['src'],
                    $rlsname
                ));
                return $response;
            }

            // find the tag
            $validTags = $sites->findValidTags($response->data['src'], $section, $rlsname);

            // filter based off tag requirements + skiplist
            $tagOptions = $settings->get('tag_options');
            foreach ($validTags as $k => $t) {
                // requirements
                if (isset($tagOptions->$t) and isset($tagOptions->$t->tag_requires)) {
                    $passes = ReleaseName::passesRequirements($rlsname, $tagOptions->$t->tag_requires, $skiplists);
                    if ($passes !== true) {
                        unset($validTags[$k]);
                        $this->container['log']->debug(sprintf(
                            'Site %s filtered tag "%s" from section "%s" for race %s because it did not meet tag requirements: %s',
                            $response->data['src'],
                            $t,
                            $section,
                            $rlsname,
                            implode(' , ', $passes)
                        ));
                    }
                }
                // skiplist
                if (isset($tagOptions->$t) and isset($tagOptions->$t->tag_skiplist)) {
                    $passes = ReleaseName::passesRegexSkiplists($rlsname, $tagOptions->$t->tag_skiplist, $skiplists);
                    if ($passes !== true) {
                        unset($validTags[$k]);
                        $this->container['log']->debug(sprintf(
                            'Site %s filtered tag "%s" from section "%s" for race %s because it matched tag skiplist: %s',
                            $response->data['src'],
                            $t,
                            $section,
                            $rlsname,
                            $passes
                        ));
                    }
                }
            }

            // reindex
            $validTags = array_values($validTags);


            // see what we are left with
            $tag = null;
            if (sizeof($validTags) > 1) {
                $this->container['log']->debug(sprintf(
                    'Site %s didn\'t start race %s because two tags matched: ' . implode(',', $validTags),
                    $response->data['src'],
                    $rlsname
                ));
                return $response;
            } elseif (sizeof($validTags) === 1) {
                $tag = $validTags[0];
            }

            // last measures
            if ($tag === null or !is_string($tag)) {
                $this->container['log']->debug(sprintf(
                    'Site %s didn\'t start race %s because no tag was found',
                    $response->data['src'],
                    $rlsname
                ));
                return $response;
            }
            if (in_array($tag, $settings->get('ignore_tags'))) {
                $this->container['log']->debug(sprintf(
                    'Site %s didn\'t start race %s because either we ignore this tag (%s) in settings file',
                    $response->data['src'],
                    $rlsname,
                    $tag
                ));
                return $response;
            }

            $raceResult = null;

            // check pretips
            $pretips = $this->container['modelsMemory']['pretips'];
            if (isset($pretips[$rlsname])) {
                $this->container['log']->debug(sprintf("Pretip found in NewRaceHandler with %d items", sizeof($pretips)));
                $raceResult = clone $pretips[$rlsname];
                unset($pretips[$rlsname]);
            }

            // check if it's raced already
            $raced = $db->fetchColumn('SELECT id FROM race WHERE rlsname = ?', array($rlsname));

            $now = (new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE'])))->format('Y-m-d H:i:s');

            // store the race id
            if (!empty($raced)) {
                $this->logRaceSite($raced, $siteName, $now);
            }

            if (empty($raced)) {
                $this->logNewRace($tag, $rlsname, $now);
                $this->logRaceSite($db->lastInsertId(), $siteName, $now);

                $this->container['log']->debug(sprintf(
                    'New race in %s / %s from %s with tag %s',
                    $section,
                    $rlsname,
                    $response->data['src'],
                    $tag
                ));

                if ($raceResult === null) {
                    $race = new \TRD\Race\Race($this->container);
                    $raceResult = $race->race($tag, $rlsname, isset($pretime['created']) ? $pretime['created'] : 0);
                } else {
                    $this->container['log']->debug(sprintf("Skipping race evaluation for %s because we already have the result from a pretip", $rlsname));
                }

                $isRace = $raceResult->isRace();

                if ($isRace) {
                    $db->update('race', array(
                      'valid_sites' => implode(',', $raceResult->validSites)
                      ,'chain' => implode(',', $raceResult->chain)
                      ,'chain_complete' => (sizeof($raceResult->affilSites) > 0 ? implode(',', $raceResult->affilSites) : null)
                      , 'updated' => $now
                      , 'log' => serialize($raceResult)
                  ), array('rlsname' => $rlsname, 'bookmark' => $tag));
                } else {
                    $db->update('race', array('log' => serialize($raceResult)), array('rlsname' => $rlsname, 'bookmark' => $tag));
                }

                if ($raceResult->isRace()) {
                    $chain = $raceResult->chain;

                    $command = new \TRD\Processor\ProcessorResponseCommand('TRADE');

                    // handle approvals
                    $approvedRow = $this->isApproved($tag, $rlsname);
                    if ($approvedRow !== false) {
                        $command = new \TRD\Processor\ProcessorResponseCommand('APPROVED');
                        $chain = explode(',', $approvedRow['chain']);
                        $db->update('approved', array('hits' => $approvedRow['hits']+1), array('id' => $approvedRow['id']));
                    }

                    if ($_ENV['AUTOTRADING_ENABLED']) {
                        $parser = new \TRD\Parser\Rules();
                        $autoResponse = $this->container['models']['autorules']->evaluate($chain, $raceResult->data);
                        if ($autoResponse !== false) {
                            $raceResult->autotraded = true;
                            $command = new \TRD\Processor\ProcessorResponseCommand('APPROVED');
                            $db->executeQuery("UPDATE race SET started = 1 WHERE rlsname = ? AND bookmark = ?", array($rlsname, $tag));
                        }
                    }

                    $this->sendtoCb($command, $raceResult);

                    $command->setDataArray([
                        'chain' => $chain
                        ,'affilSites' => $raceResult->affilSites
                        ,'bookmark' => $tag
                        ,'rlsname' => $rlsname
                    ]);

                    $response->setCommand($command);
                    $response->setMetaData($raceResult->data);
                    $response->terminate = false;

                    $prefix = sprintf(' %s ', substr(ucfirst($command->getCommand()), 0, 4));
                    $color = 'green';
                    if ($command->getCommand() === 'APPROVED') {
                        $color = 'blue';
                    }
                    ConsoleDebug::incoming(
                        (new \Malenki\Ansi($prefix))->fg('black')->bg($color)
                        .(new \Malenki\Ansi(' '.$tag.' '))->fg('black')->bg('gray')
                        . ' ' . $rlsname . ' ' .
                        (new \Malenki\Ansi('['.implode(',', $chain).']'))->fg('yellow')
                    );
                }

                return $response;
            }
        }

        return $response;
    }

    private function isApproved($tag, $rlsname)
    {
        $approved = $this->container['db']->fetchAll("
			SELECT * FROM approved
			WHERE
			  bookmark = ? AND expires > now() AND (hits < maxlimit or maxlimit = 0)
		", array($tag));
        $match = false;
        $newChain = null;
        foreach ($approved as $row) {
            $regex = null;
            switch ($row['type']) {
                case 'WILDCARD':
                    $regex = '/' . str_replace('*', '.*?', $row['pattern']) . '/i';
                break;

                case 'REGEX':
                    $regex = $row['pattern'];
                break;
            }
            if (!empty($regex) and preg_match($regex, $rlsname)) {
                return $row;
            }
        }

        return false;
    }
    
    private function scheduleIsOk()
    {
        $settings = $this->container['models']['settings'];
        $currentTimeObj = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));
        $currentDay = $currentTimeObj->format('N');
        $schedule = $settings->get('schedule')->$currentDay;
        $start = intval(str_replace(':', '', $schedule[0]));
        $end = intval(str_replace(':', '', $schedule[1]));
        $currentTime = intval($currentTimeObj->format('Hi'));
        return $currentTime >= $start and $currentTime <= $end;
    }

    private function sendtoCb($command, $raceResult)
    {
        $settings = $this->container['models']['settings'];
        $tag = $raceResult->tag;
        $rlsname = $raceResult->rlsname;
        $chain = $raceResult->chain;

        if ($command->getCommand() === 'APPROVED' and  $settings->get('approved_straight_to_cbftp') == true) {
            if ($this->scheduleIsOk()) {
                $cb = new CBFTP($settings->get('cbftp_host'), $settings->get('cbftp_port'), $settings->get('cbftp_password'));
                
                $chain = $raceResult->chain;
                $downloadOnly = null;
                if ($raceResult->hasAffils()) {
                    $chain = $raceResult->getChainWithoutAffils();
                    $downloadOnly = $raceResult->affilSites;
                }
                
                $cb->race($tag, $rlsname, $chain, $downloadOnly);
                $this->container['log']->debug(sprintf(
                    'Sent UDP packet to cbftp for rlsname %s',
                    $rlsname
                ));
            }
        }
    }

    private function logRaceSite($id, $name, $now)
    {
        try {
            $this->container['db']->insert("race_site", array(
            'race_id' => $id, 'site' => $name, 'created' => $now
          ));
        } catch (\Exception $e) {
            // just ignore it, it's no big deal, we probably matched something as new dir by accident
        }
    }

    private function logNewRace($tag, $rlsname, $now)
    {
        $this->container['db']->insert('race', array(
            'bookmark' => $tag
            , 'rlsname' => $rlsname
            , 'created' => $now
        ));
    }
}
