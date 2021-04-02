<?php

namespace TRD\DataProvider;

use TRD\Utility\ReleaseName;
use TRD\DataProvider\DataProviderResponse;

class TVMazeDataProvider extends \TRD\DataProvider\DataProvider
{
    protected $namespace = 'tvmaze';
    protected $expiry = 2592000; // 30 days
    
    const MIN_SCORE_THRESHOLD = 1;

    public function getDefaults($existing = array())
    {
        return [];
    }

    public static function getDeprecated()
    {
        return [];
    }

    public function lookup($rlsname, $forceRefresh = false)
    {
        $title = ReleaseName::getName($rlsname);

        // TODO: try and extract year, and even country to help better lookup
        $cacheResponse = parent::lookup($title, $forceRefresh);
        if ($cacheResponse->result === true) {
            if ($forceRefresh === false) {
                $generatedFields = $this->getGeneratedFields($cacheResponse->getData());
                $cacheResponse->setGeneratedFields($generatedFields);
                return $cacheResponse;
            }
        }

        if ($cacheResponse->approved) {
            $data = $this->lookupById($cacheResponse->cleanData['id']);
        } else {
            $data = $this->lookupByName($title, $rlsname);
        }
        
        if (isset($data['id'])) {
            parent::save($title, $data);
            
            // Add generated data
            $data = $this->mergeData($data, $this->getGeneratedFields($data), true);
                        
            return new DataProviderResponse(true, $data, $cacheResponse->dataImmutable, $cacheResponse->approved);
        }

        return new DataProviderResponse(false, null, $cacheResponse->dataImmutable, $cacheResponse->approved);
    }

    public function lookupById($id)
    {
        $info = array();

        $show = json_decode($this->load('https://api.tvmaze.com/shows/'.urlencode($id)), true);
        if (isset($show['id'])) {
            $info = $this->extractData($show);
        }

        return $info;
    }

    private function lookupByName($title, $rlsname)
    {
        $country = ReleaseName::getCountry($title);
        $year = ReleaseName::getYear($title);
        $strictTitle = ReleaseName::getName($rlsname, true);
        $uncleanedStrictTitle = ReleaseName::getName($rlsname);

        // remove years...
        $tokens = explode(' ', $strictTitle);
        $lastToken = $tokens[sizeof($tokens) - 1];
        if ((int) $lastToken > 1900) {
            $strictTitle = implode(' ', array_slice($tokens, 0, -1));
        }

        // clean up country for url
        if (!empty($country)) {
            $tokens = explode(' ', $strictTitle);
            $lastToken = $tokens[sizeof($tokens) - 1];
            if (strtolower($lastToken) === strtolower($country)) {
                $strictTitle = implode(' ', array_slice($tokens, 0, -1));
            }
        }

        $shows = json_decode($this->load('https://api.tvmaze.com/search/shows?q='.urlencode($strictTitle)), true);
        $this->debug[] = sprintf('Searching tvmaze with strict title [%s]', $strictTitle);

        // if we are dealing with a country version - let's check it also...
        if ($country !== null) {
            $extraShows = json_decode($this->load('https://api.tvmaze.com/search/shows?q='.urlencode($strictTitle . ' ' . $country)), true);
            $shows = array_merge($shows, $extraShows);
            $this->debug[] = sprintf('Adding results from tvmaze due to found country of [%s]', $country);
        }

        if (sizeof($shows) > 0) {
            foreach ($shows as $show) {
                $realTitle = self::realTitle($show['show']['name']);
                    
                if (!self::passesScoreThreshold($show['score'])) {
                    $this->debug[] = sprintf('Checking show title [%s] but has too low score %s', $realTitle, $score);
                    continue;
                }
                    
                $showInfo = $this->extractData($show['show']);
                    
                if ($country !== null) {
                    $this->debug[] = sprintf('Checking show title [%s] against [%s,%s]', $realTitle, $strictTitle, $uncleanedStrictTitle);
                    if (self::titlesMatch($realTitle, [$strictTitle, $uncleanedStrictTitle]) and $showInfo['country_code'] == $country) {
                        $this->debug[] = sprintf('Match (country): for show title [%s] against [%s,%s]', $realTitle, $strictTitle, $uncleanedStrictTitle);
                        return $showInfo;
                    }
                } elseif ($year !== null) {
                    $this->debug[] = sprintf('Checking show title [%s] against [%s]', $realTitle, $strictTitle);
                    if (self::titlesMatch($realTitle, [$strictTitle]) and $showInfo['premiered'] == $year) {
                        $this->debug[] = sprintf('Match (year): for show title [%s] against [%s]', $realTitle, $strictTitle);
                        return $showInfo;
                    }
                } else {
                    $this->debug[] = sprintf('Checking show title [%s] against [%s]', $realTitle, $strictTitle);
                    if (self::titlesMatch($realTitle, [$strictTitle])) {
                        $this->debug[] = sprintf('Match (year): for show title [%s] against [%s]', $realTitle, $strictTitle);
                        return $this->extractData($shows[0]['show']);
                    }
                }
            }
        }
        
        $this->debug[] = 'No results from tvmaze';

        return null;
    }
    
    private static function realTitle($title)
    {
        $realTitle = mb_strtolower($title, 'UTF-8');
        $realTitle = str_replace('&', 'and', $realTitle);
        $realTitle = preg_replace('/[^\w\d_\.\s-]+/iu', '', $realTitle);
        return ReleaseName::transliterate($realTitle);
    }
    
    private static function passesScoreThreshold($score)
    {
        return $score > self::MIN_SCORE_THRESHOLD;
    }
    
    private static function titlesMatch($realTitle, $tvmazeTitles = [])
    {
        foreach ($tvmazeTitles as $t) {
            if (strtolower($realTitle) === strtolower($t)) {
                return true;
            }
        }
        
        return false;
    }

    private function extractData($showInfo)
    {
        $info = array();

        // defaults
        $info['daily'] = false;
        $info['country'] = '';
        $info['country_code'] = '';
        $info['network'] = '';

        $info['id'] = $showInfo['id'];
        $info['url'] = $showInfo['url'];
        $info['title'] = $showInfo['name'];
        $info['classification'] = $showInfo['type'];
        $info['genres'] = $showInfo['genres'];
        $info['language'] = $showInfo['language'];
        $info['status'] = $showInfo['status'];
        $info['runtime'] = $showInfo['runtime'];
        $info['premiered'] = substr($showInfo['premiered'], 0, 4);
        $info['year'] = $info['premiered'];

        $info['total_seasons'] = 0;
        $info['latest_season'] = 0;
        $info['current_season'] = 0;
        $info['last_season'] = 0;
        $info['aired_in_last_6_months'] = false;
        $info['recent_seasons'] = array();

        if (isset($showInfo['schedule']['days']) and sizeof($showInfo['schedule']['days']) > 2) {
            $info['daily'] = true;
        }

        // basic country guessing
        if (isset($showInfo['network']['country'])) {
            $info['network'] = $showInfo['network']['name'];
            $info['country'] = $showInfo['network']['country']['name'];
            $info['country_code'] = $showInfo['network']['country']['code'];
        } elseif (isset($showInfo['webChannel']['country'])) {
            $info['network'] = $showInfo['webChannel']['name'];
            $info['country'] = $showInfo['webChannel']['country']['name'];
            $info['country_code'] = $showInfo['webChannel']['country']['code'];
        } elseif (isset($showInfo['webChannel']['name'])) {
            $info['network'] = $showInfo['webChannel']['name'];

            // fall back to wikidata
            $endpointUrl = 'https://query.wikidata.org/sparql';
            $sparqlQuery = '
              SELECT ?item ?itemLabel ?country ?countryLabel ?ISO (COUNT(?countryReference) AS ?referenceCount) WHERE {
                ?item wdt:P31 wd:Q5398426;
                      rdfs:label "' . $showInfo['name'] . '"@en.
                OPTIONAL {
                  ?item p:P495 ?countryStatement.
                  ?countryStatement ps:P495 ?country.
                  ?country wdt:P297 ?ISO.
                  OPTIONAL { ?countryStatement prov:wasDerivedFrom ?countryReference. }
                }
                SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
              }
              GROUP BY ?item ?itemLabel ?country ?countryLabel ?ISO
              ORDER BY DESC(?referenceCount)
            ';

            $contents = json_decode($this->load($endpointUrl . '?query=' . urlencode($sparqlQuery) . '&format=json'), true);
            if (isset($contents['results']['bindings']) and isset($contents['results']['bindings'][0]['countryLabel']) and isset($contents['results']['bindings']['0']['ISO'])) {
                $country = $contents['results']['bindings'][0]['countryLabel']['value'];
                switch ($country) {
                  case 'United States of America':
                    $country = 'United States';
                  break;
                  default:
                }
                $info['country'] = $country;
                $info['country_code'] = $contents['results']['bindings']['0']['ISO']['value'];
            }
        }

        // clean up shit country codes...
        if ($info['country_code'] == 'GB') {
            $info['country_code'] = 'UK';
        }

        // check current season
        $episodes = json_decode($this->load('https://api.tvmaze.com/shows/'.urlencode($info['id']).'/episodes'), true);
        if (sizeof($episodes) > 0) {
            $mostRecentEpisodeData = $episodes[sizeof($episodes) - 1];
            $info['latest_season'] = $mostRecentEpisodeData['season'];
            $mostRecentEpisodeAirDate = new \DateTime($mostRecentEpisodeData['airdate'], new \DateTimeZone($_ENV['APP_TIMEZONE']));
            $sixMonthsAgo = new \DateTime('-6 months', new \DateTimeZone($_ENV['APP_TIMEZONE']));
            if ($mostRecentEpisodeAirDate > $sixMonthsAgo) {
                $info['aired_in_last_6_months'] = true;
            }

            if (in_array($info['status'], array('To Be Determined', 'In Development', 'Running', 'Ending')) and $info['aired_in_last_6_months']) {
                $info['current_season'] = $mostRecentEpisodeData['season'];
                $info['last_season'] = ($info['current_season'] == 1 ? 0 : ($info['current_season'] - 1));
            }
        }

        $seasons = json_decode($this->load('https://api.tvmaze.com/shows/'.urlencode($info['id']).'/seasons'), true);
        if (sizeof($seasons) > 0) {
            $info['total_seasons'] = $seasons[sizeof($seasons) - 1]['number'];

            // get recent seasons
            $nineMonthsAgo = new \DateTime('-9 months', new \DateTimeZone($_ENV['APP_TIMEZONE']));
            $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));
            foreach ($seasons as $season) {
                if ($season['endDate'] !== null) {
                    $ended = new \DateTime($season['endDate'], new \DateTimeZone($_ENV['APP_TIMEZONE']));
                    if ($ended > $nineMonthsAgo) {
                        $info['recent_seasons'][] = (int)$season['number'];
                    }
                }
            }
        }

        // check web show
        $info['web'] = false;
        if ($showInfo['network'] == null and isset($showInfo['webChannel']['id'])) {
            $info['web'] = true;
        }

        // over-ride classification to "Children" if we are a shit network or a kid-related genre
        $settings = $this->container['models']['settings']->getData();
        if (
          isset($settings->children_networks) and is_array($settings->children_networks) and
          in_array($info['network'], $settings->children_networks)) {
            $info['genres'][] = 'Children';
        }

        return $info;
    }
    
    public static function getGeneratedFields($data)
    {
        $new = ['is_scripted_english' => false];
        if ($data['classification'] == 'Scripted' and in_array($data['country'], array('United States', 'Canada', 'New Zealand', 'United Kingdom')) && $data['language'] == 'English') {
            $new['is_scripted_english'] = true;
        }
      
        return $new;
    }
}
