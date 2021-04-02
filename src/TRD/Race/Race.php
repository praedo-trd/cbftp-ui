<?php

namespace TRD\Race;

use TRD\DataProvider\IMDBDataProvider;
use TRD\DataProvider\ReleaseNameDataProvider;
use TRD\DataProvider\BoxOfficeMojoDataProvider;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\DataProvider\MusicDataProvider;
use TRD\Race\RaceResult;
use TRD\Utility\ReleaseName;

class Race
{
    private $container;
    private $sites = array();
    private $useCache = true;

    private $containerModels = array();

    public function __construct($container, $useCache = true)
    {
        $this->container = $container;
        $this->containerModels = $container['models'];
        $this->useCache = $useCache;
    }

    public function addSites($sites)
    {
        $this->sites = $sites;
    }

    public function race($tag, $rlsname, $preTime = 0)
    {
        $result = new RaceResult();
        $result->tag = $tag;
        $result->rlsname = $rlsname;

        $sites = $this->containerModels['sites'];
        $skiplists = $this->containerModels['skiplists'];
        $settings = $this->containerModels['settings'];
//        $sections = $this->container['models']['sections'];

        // add all sites if we don't add manually
        if (sizeof($this->sites) == 0) {
            foreach ($sites->getData() as $siteName => $info) {
                $this->sites[] = $siteName;
            }
        }

        $preDifferenceInSeconds = 0;
        if ($preTime != null) {
            $preDifferenceInSeconds = $this->container['db']->fetchColumn("
                SELECT TIME_TO_SEC(TIMEDIFF(NOW(), ?)) AS diff
            ", array($preTime));
        }

        $addAffils = false;
        if ($settings->exists('always_add_affils') and $settings->get('always_add_affils') === true) {
            $addAffils = true;
        }

        $group = ReleaseName::getGroup($rlsname);

        $chain = array();
        foreach ($this->sites as $siteName) {
            $siteInfo = $sites->getSite($siteName);
            if (isset($siteInfo->sections) and is_array($siteInfo->sections) and $siteInfo->enabled) {
                foreach ($siteInfo->sections as $ss) {
                    if (isset($ss->tags) and is_array($ss->tags) and sizeof($ss->tags) > 0) {
                        foreach ($ss->tags as $st) {

                            // check tag matches, trigger matches
                            if ($st->tag == $tag and preg_match($st->trigger, $rlsname)) {

                                // check pretime
                                if (
                                    !isset($ss->pretime)
                                    or
                                    (isset($ss->pretime) && $ss->pretime > 0 && $preDifferenceInSeconds < (int)$ss->pretime*60)
                                    or
                                    $ss->pretime == 0
                                ) {
                                    $chain[] = array(
                                        'name' => $siteName
                                        ,'section' => $ss->name
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        // clean some data up
        $cleanedRlsname = ReleaseName::getName($rlsname);

        // rule parser..
        $parser = new \TRD\Parser\Rules();
        $raceData = new RaceResultData(array(
          'rlsname' => $rlsname,
          'tag' => $tag
        ));

        $dataProvider = new ReleaseNameDataProvider($this->container);
        $releaseNameDataProviderResponse = $dataProvider->lookup($rlsname);
        $raceData->attachDataProviderResponse('rlsname', $releaseNameDataProviderResponse);

        $dateTimeDataProvider = new \TRD\DataProvider\DateTimeDataProvider($this->container);
        $dateTimeDataProviderResponse = $dateTimeDataProvider->lookup($rlsname);
        $raceData->attachDataProviderResponse('datetime', $dateTimeDataProviderResponse);

        // global bad dir
        if (preg_match($settings->get('baddir'), $rlsname)) {
            $result->catastrophes[] = 'Bad dir match based on regex: ' . $settings->get('baddir');
            $result->data = $raceData;
            $result->endRace();
            $this->container['log']->info(print_r($result, true));
            return $result;
        }

        // check global settings before any complex data lookups
        if ($settings->exists('banned_groups')) {
            $bannedGroups = array_map('strtoupper', $settings->get('banned_groups'));
            $group = ReleaseName::getGroup($rlsname);
            if (in_array(strtoupper($group), $bannedGroups)) {
                $result->catastrophes[] = 'Banned group: ' . $group;
                $result->data = $raceData;
                $result->endRace();
                $this->container['log']->info(print_r($result, true));
                return $result;
            }
        }

        // get tag options ready for everything
        $tagOptions = $settings->get('tag_options');

        if ($tagOptions !== null && isset($tagOptions->$tag->allowed_groups) && sizeof($tagOptions->$tag->allowed_groups) > 0) {
            $group = strtoupper(ReleaseName::getGroup($rlsname));
            $allowedGroups = array_map('trim', array_map('strtoupper', $tagOptions->$tag->allowed_groups));
            if (!in_array($group, $allowedGroups)) {
                $result->catastrophes[] = $group . ' is not in allowed list of groups for this tag: ' . implode(',', $tagOptions->$tag->allowed_groups);
                $result->data = $raceData;
                $result->endRace();
                $this->container['log']->info(print_r($result, true));
                return $result;
            }
        }

        // check the tag requirements
        if (isset($tagOptions->$tag->tag_requires) and is_array($tagOptions->$tag->tag_requires)) {
            $passes = ReleaseName::passesRequirements($rlsname, $tagOptions->$tag->tag_requires, $skiplists);
            if ($passes !== true) {
                $result->catastrophes[] = 'Tag requirements were not met: ' . implode(',', $passes);
                $result->data = $raceData;
                $result->endRace();
                $this->container['log']->info(print_r($result, true));
                return $result;
            }
        }

        // check the tag skiplist
        if (isset($tagOptions->$tag->tag_skiplist) and is_array($tagOptions->$tag->tag_skiplist)) {
            $passes = ReleaseName::passesRegexSkiplists($rlsname, $tagOptions->$tag->tag_skiplist, $skiplists);
            if ($passes !== true) {
                $result->catastrophes[] = 'Tag skiplist item matched: ' . $passes;
                $result->data = $raceData;
                $result->endRace();
                $this->container['log']->info(print_r($result, true));
                return $result;
            }
        }

        // check tag options for data sources
        $datalookupStart = microtime(true);
        $tagDataSources = (isset($tagOptions->$tag->data_sources) && sizeof($tagOptions->$tag->data_sources) > 0) ? $tagOptions->$tag->data_sources : null;
        $usingTVMazeDataProvider = false;
        if ($tagDataSources !== null) {
            foreach ($tagDataSources as $dataSource) {
                switch ($dataSource) {
                    case 'tvmaze':
                        $usingTVMazeDataProvider = true;
                        $dataProvider = new TVMazeDataProvider($this->container);
                        $dataResponse = $dataProvider->lookup($rlsname, !$this->useCache);

                        if (!$dataResponse->result) {
                            $this->container['datalog']->notice('No tvmaze info found for ' . $rlsname);
                        }

                        $raceData->attachDataProviderResponse('tvmaze', $dataResponse);
                    break;

                    case 'music':
                        $dataProvider = new MusicDataProvider($this->container);
                        $dataResponse = $dataProvider->lookup($rlsname);
                        $raceData->attachDataProviderResponse('music', $dataResponse);
                    break;

                    case 'imdb':
                        $imdbDataProvider = new IMDBDataProvider($this->container);
                        $imdbResponse = $imdbDataProvider->lookup($rlsname, !$this->useCache);

                        if (!$imdbResponse->result) {
                            $this->container['datalog']->notice('No imdb info found for ' . $rlsname);
                        }
                        $raceData->attachDataProviderResponse('imdb', $imdbResponse);

                        $bomDataProvider = new BoxOfficeMojoDataProvider($this->container);
                        $bomResponse = $bomDataProvider->lookup($rlsname, !$this->useCache, $imdbResponse->getData());
                        if (!$bomResponse->result) {
                            $this->container['datalog']->notice('No bom info found for ' . $rlsname);
                        }
                        $raceData->attachDataProviderResponse('bom', $bomResponse);

                    break;
                }
            }
        }
        $result->dataLookupDuration = round(microtime(true) - $datalookupStart, 3)*1000;

        // check dupe info
        $dupeEngine = null;
        if ($usingTVMazeDataProvider) {
            $ffs = '';
            $rlsnameInfo = $releaseNameDataProviderResponse->getData();
            if (empty($rlsnameInfo['resolution'])) {
                $ffs = 'OR dupe_resolution IS NULL';
            }
            $possibleSources = $this->container['db']->fetchAll("
            SELECT rlsname, created FROM pre WHERE
              dupe_k = ? AND
              dupe_season_episode IS NOT NULL AND
              dupe_season_episode = ? AND
              (dupe_resolution = ? $ffs)
          ", array($cleanedRlsname, $rlsnameInfo['season'].'_'.$rlsnameInfo['episode'], $rlsnameInfo['resolution']));

            $dupeEngine = new \TRD\DupeEngine\Engine(array());
            $dupeEngine->addFilterRegex($settings->get('baddir'));

            // add tag_skiplist while we're here
            if (isset($tagOptions->$tag->tag_skiplist) and is_array($tagOptions->$tag->tag_skiplist)) {
                $tagSkiplist = $tagOptions->$tag->tag_skiplist;
                foreach ($tagSkiplist as $sl) {
                    $dupeEngine->addFilterRegex($sl);
                }
            }

            foreach ($possibleSources as $dupeSource) {
                $src = new \TRD\DupeEngine\Source($dupeSource['rlsname']);
                $dupeEngine->addSource($src);
                $result->dupeEngineSources[] = $dupeSource;
                $raceData->attachDupeSource($src);
            }
        }

        // add our race data to the parser as rule data
        $parser->addData($raceData->toRuleData());
        $result->data = $raceData;

        // loop through the incoming sites and parse rules
        $addedSites = array();
        foreach ($chain as $site) {

            // get skiplists + rules for this site
            $sectionRules = $this->_getSectionRules($site['name'], $site['section']);
            $tagRules = $this->_getTagRules($site['name'], $site['section'], $tag);
            $sls = $this->_getSkiplists($site['name'], $site['section']);
            $sectionDupeRules = $this->_getSectionDupeRules($site['name'], $site['section']);

            $valid = true;
            $invalidReasons = array();

            // handle banned groups
            $sn = $site['name'];
            if (isset($sites->getData()->$sn->banned_groups) and sizeof($sites->getData()->$sn->banned_groups) > 0 and in_array(strtoupper($group), $sites->getData()->$sn->banned_groups)) {
                $valid = false;
                $invalidReasons[] = $group . ' is a banned group for this site';
            }

            // handle section rules
            if ($sectionRules !== null) {
                $sectionRules = $parser->sortRules($sectionRules);
                foreach ($sectionRules as $rule) {
                    $rule = trim($rule);

                    if (!empty($rule)) {
                        try {
                            if ($parser->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsFalse) {
                                $valid = false;
                                $invalidReasons[] = "Failed section rule: $rule";
                            } elseif ($parser->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsExcept) {
                                $valid = true;
                                $result->exceptions[] = array(
                                  'site' => $site['name']
                                  ,'exception' => $rule
                              );
                                break;
                            } elseif ($parser->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsComment) {
                                continue;
                            }
                        } catch (\TRD\Parser\InvalidRule $e) {
                            $valid = false;
                            $invalidReasons[] = "Invalid section rule: $rule";
                        }
                    }
                }
            }

            // handle tag rules
            if ($tagRules !== null) {
                $tagRules = $parser->sortRules($tagRules);
                foreach ($tagRules as $rule) {
                    $rule = trim($rule);

                    if (!empty($rule)) {
                        try {
                            if ($parser->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsFalse) {
                                $valid = false;
                                $invalidReasons[] = "Failed tag rule: $rule";
                            } elseif ($parser->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsExcept) {
                                $valid = true;
                                $result->exceptions[] = array(
                                  'site' => $site['name']
                                  ,'exception' => $rule
                              );
                                break;
                            }
                        } catch (\TRD\Parser\InvalidRule $e) {
                            $valid = false;
                            $invalidReasons[] = "Invalid tag rule: $rule";
                        }
                    }
                }
            }

            if ($sectionRules === null && $tagRules === null) {
                $valid = false;
                $invalidReasons[] = "No section or tag rules set";
            }

            // handle skiplists after rules, because skiplists can apply to exceptions
            if ($sls !== null) {
                foreach ($sls as $skiplist) {
                    $passesSkiplist = $skiplists->passesSkiplist($skiplist, $rlsname);
                    if ($passesSkiplist !== true) {
                        $valid = false;
                        $invalidReasons[] = $passesSkiplist;
                    }
                }
            }

            // dupe rules
            if ($sectionDupeRules !== null and $dupeEngine !== null) {
                if (isset($sectionDupeRules->{'source.firstWins'}) and $sectionDupeRules->{'source.firstWins'} === true) {
                    $dupeResult = $dupeEngine->isDupe($rlsname, 'source.firstWins');
                    if ($dupeResult->isDupe()) {
                        $valid = false;
                        $invalidReasons[] = "Failed first format wins. Previous releases: " . $dupeResult->getSourcesAsString();
                    }
                } elseif (isset($sectionDupeRules->{'source.priority'}) and !empty($sectionDupeRules->{'source.priority'})) {
                    $dupeResult = $dupeEngine->isDupe($rlsname, 'source.priority', array('priority' => $sectionDupeRules->{'source.priority'}));
                    if ($dupeResult->isDupe()) {
                        $valid = false;
                        $invalidReasons[] = "Failed dupe priority. Previous releases: " . $dupeResult->getSourcesAsString();
                    }
                }
            }

            // add affils if possible
            if ($addAffils and in_array(strtoupper($group), $sites->getAffils($site['name']))) {
                $valid = true;
                $bnc = $this->_getBNC($site['name'], $site['section']);
                if ($bnc !== null) {
                    $result->affilSites[] = $bnc;
                } else {
                    $result->affilSites[] = $site['name'];
                }
            }

            // determine if it's valid or not!
            if ($valid) {
                $result->validSites[] = $site['name'];
                $bnc = $this->_getBNC($site['name'], $site['section']);
                if ($bnc !== null) {
                    $result->chain[] = $bnc;
                } else {
                    $result->chain[] = $site['name'];
                }

                // This array is only populated if we add an affil to a race regardless of the fact it broke the rules
                if (sizeof($invalidReasons) > 0) {
                    $result->invalidSitesOverrides[] = array(
                      'site' => $site['name']
                      ,'section' => $site['section']
                      ,'invalidReasons' => $invalidReasons
                  );
                }
            } else {
                $result->invalidSites[] = array(
                    'site' => $site['name']
                    ,'section' => $site['section']
                    ,'invalidReasons' => $invalidReasons
                );
            }
        }

        // check that the race doesn't consist solely of affil sites
        if (sizeof($result->affilSites) > 0 and sizeof($result->affilSites) === sizeof($result->validSites)) {
            $result->catastrophes[] = 'Only affil sites were available to complete this race';
            $result->endRace();
            $this->container['log']->info(print_r($result, true));
            return $result;
        }

        $result->endRace();

        $this->container['log']->info(print_r($result, true));

        return $result;
    }
    
    private function _getSectionRules($site, $section)
    {
        $sites = $this->containerModels['sites'];
        foreach ($sites->getSite($site)->sections as $s) {
            if ($s->name == $section and isset($s->rules) and sizeof($s->rules) > 0) {
                return $s->rules;
            }
        }
        return null;
    }

    private function _getSectionDupeRules($site, $section)
    {
        $sites = $this->containerModels['sites'];
        foreach ($sites->getSite($site)->sections as $s) {
            if ($s->name == $section and isset($s->dupeRules)) {
                return $s->dupeRules;
            }
        }
        return null;
    }

    private function _getTagRules($site, $section, $tag)
    {
        $sites = $this->containerModels['sites'];
        foreach ($sites->getSite($site)->sections as $s) {
            if ($s->name == $section and isset($s->tags) and sizeof($s->tags) > 0) {
                foreach ($s->tags as $t => $ti) {
                    if ($ti->tag === $tag and isset($ti->rules) and sizeof($ti->rules) > 0) {
                        return $ti->rules;
                    }
                }
            }
        }
        return null;
    }

    private function _getSkiplists($site, $section)
    {
        $sites = $this->containerModels['sites'];
        foreach ($sites->getSite($site)->sections as $s) {
            if ($s->name == $section and isset($s->skiplists) and sizeof($s->skiplists) > 0) {
                return $s->skiplists;
            }
        }
        return null;
    }

    private function _getBNC($site, $section)
    {
        $sites = $this->containerModels['sites'];
        foreach ($sites->getSite($site)->sections as $s) {
            if ($s->name == $section and isset($s->bnc) and !empty($s->bnc)) {
                return $s->bnc;
            }
        }
        return null;
    }
}
