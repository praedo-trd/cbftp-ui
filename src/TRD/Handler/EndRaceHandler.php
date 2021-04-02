<?php

namespace TRD\Handler;

use TRD\Processor\ProcessorResponse;
use TRD\Processor\ProcessorResponseCommand;
use TRD\Model\Sites;
use TRD\Utility\ReleaseName;
use TRD\Utility\ConsoleDebug;

class EndRaceHandler extends \TRD\Handler\Handler
{
    public function handle(ProcessorResponse $response)
    {
        if (!isset($response->data['src'])) {
            return $response;
        }

        $db = $this->container['db'];
        ;

        $str = $response->data['msg'];

        $section = null;
        $rlsname = null;

        // check each sites strings
        $sites = $this->container['models']['sites'];
        $siteName = $response->data['src'];

        $siteInfo = $sites->getSite($siteName);

        $extraction = \TRD\Utility\IRCExtractor::extract($siteInfo->irc->strings, array('endstring'), $str);
        $section = $extraction['section'];
        $rlsname = $extraction['rlsname'];

        // we have a match
        if ($rlsname !== null) {

            //ConsoleDebug::debug("End race in $section / $rlsname from " . $response->data['src']);

            $rlsname = ReleaseName::getReleaseNameFromDirectory($rlsname);

            $raceInfo = $db->fetchAssoc("SELECT id, bookmark, chain, chain_complete FROM race WHERE rlsname = ?", array($rlsname));
            if (!empty($raceInfo['chain'])) {
                if (!empty($raceInfo['chain_complete'])) {
                    $currentlyComplete = explode(',', trim($raceInfo['chain_complete']));
                } else {
                    $currentlyComplete = array();
                }

                // get the bnc from ths section if we can
                $possibleBNCs = array();
                if ($section !== null and isset($siteInfo->sections->$section)) {
                    $section = $siteInfo->sections->$section;
                    foreach ($section->tags as $t) {
                        if ($t->tag == $raceInfo['bookmark'] and isset($section->bnc) and !empty($section->bnc)) {
                            $possibleBNCs[] = $section->bnc;
                        } else {
                            $possibleBNCs[] = $siteName;
                        }
                    }
                } else {
                    // try and get it by iterating over sections with tags that match
                    foreach ($siteInfo->sections as $section) {
                        foreach ($section->tags as $t) {
                            if ($t->tag == $raceInfo['bookmark']) {
                                if (isset($section->bnc) and !empty($section->bnc)) {
                                    $possibleBNCs[] = $section->bnc;
                                } else {
                                    $possibleBNCs[] = $siteName;
                                }
                            }
                        }
                    }
                }

                $this->container['log']->debug(sprintf(
                  'Site %s found end race string for rlsname %s and bookmark %s with possible BNC\'s "%s"',
                  $response->data['src'],
                  $rlsname,
                  $raceInfo['bookmark'],
                  implode(',', $possibleBNCs)
              ));

                if (sizeof($possibleBNCs) !== 1) {
                    return $response;
                }

                $currentlyComplete[] = $possibleBNCs[0];
                $currentlyComplete = array_unique($currentlyComplete);
                sort($currentlyComplete);

                $db->update('race', array('chain_complete' => implode(',', $currentlyComplete)), array('rlsname' => $rlsname));

                $now = $db->fetchColumn('SELECT NOW()');
                $db->update('race_site', array(
                'ended' => $now
              ), array(
                'race_id' => $raceInfo['id'], 'site' => $siteName
              ));

                $command = new \TRD\Processor\ProcessorResponseCommand('RACECOMPLETESTATUS');
                $command->setData('rlsname', $rlsname);
                $command->setData('chain_complete', $currentlyComplete);
                $response->setCommand($command);
            } else {
                $this->container['log']->debug(sprintf(
                  'Site %s found end race string for rlsname %s but no race was found',
                  $response->data['src'],
                  $rlsname
              ));
            }

            return $response;
        }

        return $response;
    }
}
