<?php

namespace TRD\DataProvider;

use TRD\Utility\ReleaseName;
use Symfony\Component\DomCrawler\Crawler;

class IMDBDataProvider extends \TRD\DataProvider\DataProvider
{
    protected $namespace = 'imdb';

    private $bingItems;
    private $imdbResponse;

    const BING_USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';
    const IMDB_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36';

    public function getDefaults($existing = array())
    {
        $info = $existing;
        $info['id'] = $existing['imdbid'];
        $info['url'] = 'http://www.imdb.com/title/' . $existing['imdbid'] . '/';
        $info['genres'] = array();
        $info['language_primary'] = '';
        $info['language'] = '';
        $info['languages'] = array();
        $info['country'] = 'n/a';
        $info['countries'] = array();
        $info['votes'] = 0;
        $info['stv'] = false;
        $info['limited'] = false;
        $info['series'] = false;
        $info['rating'] = 0;
        $info['runtime'] = 0;
        $info['title'] = '';
        $info['aka'] = '';
        $info['year'] = '';
        $info['poster_hash'] = '';
        $info['screens_uk'] = 0;
        $info['screens_us'] = 0;
        return $info;
    }

    public static function getDeprecated()
    {
        return array(
          'screens_us', 'screens_uk', 'limited'
        );
    }

    public function lookup($rlsname, $forceRefresh = false)
    {
        $title = ReleaseName::getName($rlsname);
        $this->debug[] = sprintf('Converted %s to %s', $rlsname, $title);

        $cacheResponse = parent::lookup($title, $forceRefresh);
        if ($cacheResponse->result === true) {
            if ($forceRefresh === false) {
                $this->debug[] = sprintf('Cache hit for %s', $title);
                return $cacheResponse;
            }
        }

        $possibleIMDBIDs = $data = array();
        if ($cacheResponse->approved) {
            $this->debug[] = sprintf('approved flag set to 1 for %s', $title);
            $data = $this->extractDataFromIMDBId($cacheResponse->cleanData['id']);
            if (!empty($data['imdbid'])) {
                $this->debug[] = sprintf('Directly retrieved data from IMDB using ID %s for %s', $cacheResponse->cleanData['id'], $title);
                parent::save($title, $data);
                return new DataProviderResponse(true, $data, $cacheResponse->dataImmutable, $cacheResponse->approved);
            }
        } else {
            $this->debug[] = sprintf('Finding possible IMDB ids for %s', $title);
            $possibleIMDBIDs = $this->findPossibleIMDBIDs($title);
            $this->debug[] = sprintf('Found %d possible IMDB ids', sizeof($possibleIMDBIDs));
            if ($possibleIMDBIDs === false) {
                $this->debug[] = sprintf('No IMDB ids for %s', $title);
                return new DataProviderResponse(false, null, $cacheResponse->dataImmutable, $cacheResponse->approved);
            }
        }

        if (sizeof($possibleIMDBIDs) == 1) {
            $data = $this->extractDataFromIMDBId($possibleIMDBIDs[0]);
        } elseif (sizeof($possibleIMDBIDs) > 1) {
            $data = $this->extractDataFromIMDBIDs($possibleIMDBIDs, $title);
        }

        if (!empty($data['imdbid'])) {
            parent::save($title, $data);
            return new DataProviderResponse(true, $data, $cacheResponse->dataImmutable, $cacheResponse->approved);
        }

        return new DataProviderResponse(false, null, $cacheResponse->dataImmutable, $cacheResponse->approved);
        ;
    }

    private function _clean_movie_title($title)
    {
        $title = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
        }, $title);
        $title = str_ireplace(' - IMDb', '', $title);
        $title = str_ireplace('IMDb: ', '', $title);
        $title = str_replace('?', '', $title);
        $title = preg_replace('/-\s+(\d+)$/i', '\1', $title);
        $title = preg_replace('/\((\d{4})\)/i', '\1', $title);
        $title = preg_replace('/\(.*?\)$/i', '', $title);
        $title = preg_replace('/&\s+/', 'and ', $title);
        $title = preg_replace('/&amp;\s+/', 'and ', $title);
        $title = preg_replace("/&[a-z]+;/", '', $title);
        $title = preg_replace('/(:|!)/i', '', $title);
        $title = preg_replace('/([^\.\s]+)[\.,]/i', '\1', $title);
        $title = str_replace(', ', ' ', $title);
        $title = str_replace(' - ', ' ', $title);
        $title = preg_replace('/\s{2,}/', ' ', $title);
        return str_replace("'", '', trim($title));
    }

    public function getBingItems()
    {
        return $this->bingItems;
    }

    public function findPossibleIMDBIDs($movieTitle)
    {
        $url = 'https://www.bing.com/search?q=' . urlencode($movieTitle . ' site:imdb.com');
        $response = $this->load($url, self::BING_USER_AGENT);
        
        $this->debug[] = sprintf('Searching bing using url %s', $url);

        $list = preg_match('/<ol.*?>.*?<\/ol>/is', $response, $matches);
        if (empty($matches)) {
            return false;
        }

        $crawler = new Crawler($response);
        $listItems = array();
        foreach ($crawler->filter('ol#b_results > li a:first-of-type') as $domElement) {
            if ($domElement->getAttribute('class') !== 'b_ans') {
                $html = $domElement->ownerDocument->saveHTML($domElement);
                $listItems[] = $html;
            }
        }
        $response = implode('', $listItems);
        
        $this->bingItems = $listItems;

        preg_match_all('/imdb\.com\/title\/(tt\d+)\/?".*?>(.*?)<\/a/is', $response, $matches);

        $imdbid = null;
        $possible = array();

        foreach ($matches[1] as $k => $id) {

            
            
            // skip shit
            if (strpos($id, '<img') !== false) {
                continue;
            }
            $possibleTitle = $matches[2][$k];
            $possibleTitle = preg_replace('/<div.*?class="b_attribution".*?>.*?<\/div>/is', '', $possibleTitle);

            $url = $matches[1][$k];
            $title = trim(preg_replace('/\s\s+/', ' ', strip_tags($possibleTitle)));
            $title = $this->_clean_movie_title($title);
            
            $this->debug[] = sprintf('Examining serp title [%s] with IMDB id %s', $title, $id);

            // dont continue past the third result - probably some crap
            if ($k > 3) {
                break;
            }

            // check title
            if (preg_match('/^' . preg_quote($movieTitle) . '$/i', $title)) {
                $this->debug[] = sprintf('Examining title [%s] with IMDB id %s', $title, $id);
                if (sizeof($possible) < 2) {
                    $possible[] = $id;
                    $this->debug[] = sprintf('Match: Rlsname [%s] (ID %s) matched serp title of [%s]', $movieTitle, $id, $title);
                }
            } else {
                $this->debug[] = sprintf('Fail: Rlsname [%s] (ID %s) did not match serp title of [%s]', $movieTitle, $id, $title);
            }
        }
                
        if ($imdbid !== null) {
            return array($imdbid);
        } else {
            return array_unique(array_slice($possible, 0, 2));
        }
    }

    public function findIMDBID($movieTitle)
    {
        $url = 'https://www.bing.com/search?q=' . urlencode($movieTitle . ' site:imdb.com');
        $response = $this->load($url, self::BING_USER_AGENT);
        preg_match_all('/imdb\.com\/title\/(tt\d+)\/?".*?>(.*?)<\/a/is', $response, $matches);

        // clean off the year to aid lookups
        // $bits = explode(' ', $movieTitle);
        // if((int)$bits[sizeof($bits)-1] > 1900) {
        //     $movieTitle = implode(' ', array_slice($bits, 0, -1));
        // }

        $imdbid = null;
        foreach ($matches[1] as $k => $v) {

            // skip shit
            if (strpos($v, '<div') !== false) {
                continue;
            }

            $url = $matches[1][$k];
            $title = trim(preg_replace('/\s\s+/', ' ', strip_tags($matches[2][$k])));
            $title = $this->_clean_movie_title($title);

            // check title
            if (!preg_match('/^' . preg_quote($movieTitle) . '$/i', $title)) {
                continue;
            }

            $imdbid = $v;
        }

        if ($imdbid !== null) {
            // fix for leading zero bug
            $imdbid = preg_replace('/tt[0]+/i', 'tt0', $imdbid);
            return $imdbid;
        }

        return false;
    }

    private function extractDataFromIMDBIDs($ids, $title)
    {
        $data = array();
        foreach ($ids as $id) {
            $data["$id"] = $this->extractDataFromIMDBId($id);
        }

        $matches = array();
        foreach ($data as $id => $imdbInfo) {
            $imdbTitle = $this->_clean_movie_title($imdbInfo['title']);
            if (
              preg_match('/^' . $title . '$/i', $imdbInfo['aka'] . ' ' . $imdbInfo['year']) or
              preg_match('/^' . $title . '$/i', $imdbTitle . ' ' . $imdbInfo['year'])
            ) {
                $this->debug[] = sprintf('Title or aka match on IMDB page for id %s', $id);
                $matches[] = $imdbInfo;
            }
        }

        if (sizeof($matches) == 1) {
            $this->debug[] = sprintf('Choosing match %s', $id);
            return $matches[0];
        }

        $this->debug[] = 'Will not match because found multiple matching IDs';
        return null;
    }

    public function extractDataFromIMDBId($imdbid)
    {
        $this->debug[] = sprintf('Extracting IMDB information for id %s', $imdbid);
        $response = $this->load('http://www.imdb.com/title/' . $imdbid . '/', self::IMDB_USER_AGENT);

        $info = $this->getDefaults(array('imdbid' => $imdbid));

        if (!is_string($response)) {
            return $info;
        }

        $crawler = new Crawler($response);
        
        // get real id
        if (preg_match('/property="pageId" content="(tt\d+)"/i', $response, $idmatches)) {
            $info['id'] = $idmatches[1];
            $info['imdbid'] = $idmatches[1];
        }

        // get full title
        if (preg_match('/og:title.*?content="(.*?)"/is', $response, $titlematch)) {
            $info['title'] = trim(html_entity_decode($titlematch[1]));

            $info['title'] = str_ireplace(' - IMDb', '', $info['title']);

            // clean up bits from title
            $info['title'] = preg_replace(
                array('/[\r\n]+/i', '/\(\d+\)/i', '/<span.*?>.*?<\/span>/is', '/\(\d+-\d+\)/i'),
                array(' ', '', '', ''),
                $info['title']
            );

            $info['title'] = trim(strip_tags(preg_replace('/[\r\n]+/i', '', $info['title'])));
        }

        // aka
        if (preg_match('/originalTitle">(.*?)</i', $response, $akamatch)) {
            $info['aka'] = trim(strip_tags($akamatch[1]));
        }

        // get STV
        if (preg_match('/<title>(.*?)<\/title>/is', $response, $stvmatch)) {
            if (strpos($stvmatch[1], '(V)') !== false or strpos($stvmatch[1], '(TV') !== false or preg_match('/\(Video \d+/is', $stvmatch[1])) {
                $info['stv'] = true;
            }
            if (strpos($stvmatch[1], '(TV Series') !== false) {
                $info['series'] = true;
            }
        }

        // get year
        /*
        if (preg_match('/<a href="\/year\/([0-9]+)\/?">.*?<\/a>/i', $response, $yearmatch)) {
            $info['year'] = $yearmatch[1];
        }
        */
        if (preg_match('/<title>.*?\((\d{4})\).*?<\/title>/is', $response, $yearmatch)) {
            $info['year'] = $yearmatch[1];
        }

        // get director
        /*if(preg_match('/Directed by.*?<a href="(.*?)">(.*?)<\/a>/is', $response, $directormatch))
      {
          $info['director']['name'] = $directormatch[2];
          $info['director']['url'] = 'http://us.imdb.com' . $directormatch[1];
      }*/

        // get genres
        if (preg_match('/Genres:<\/h4>(.*?)<\/div>/is', $response, $genrematch)) {
            preg_match_all('/<a.*?>(.*?)<\/a>/is', $genrematch[1], $genrematches);
            if (sizeof($genrematches) > 0) {
                $genres = array();
                foreach ($genrematches[1] as $v) {
                    $genres[] = trim($v);
                }
                $info['genres'] = array_unique($genres);
            }
        }

        // get tagline
        if (preg_match('/Tagline:<\/b> (.*?)</is', $response, $taglinematch)) {
            $info['tagline'] = $taglinematch[1];
        }

        // get plot
        if (preg_match('/Plot Outline:<\/b> (.*?)</i', $response, $plotmatches)) {
            /*if(!empty($plotmatches[3]))
           {
               $info['plot'] = $plotmatches[1] . ' <a href="http://us.imdb.com' . $plotmatches[3] . '">(more)</a> <a href="http://us.imdb.com' . $plotmatches[4] . '">(view trailer)</a>';
           }
           else
           {
               $info['plot'] = $plotmatches[1] . ' <a href="http://us.imdb.com' . $plotmatches[4] . '">(view trailer)</a>';
           }*/
            $info['plot'] = $plotmatches[1];
        }

        // get votes
        if (preg_match('/itemprop="ratingValue".*?>(.*?)</im', $response, $ratingMatch)) {
            $info['rating'] = trim($ratingMatch[1]);
        }


        if (preg_match('/itemprop="ratingCount".*?>(.*?)</im', $response, $votesMatch)) {
            $info['votes'] = trim(str_replace(',', '', $votesMatch[1]));
        }

        // get image
        if (preg_match('/class="poster".*?>.*?src="(.*?)"/is', $response, $imagematch)) {
            $info['imageurl'] = $imagematch[1];
            $bits = explode('/', $imagematch[1]);
            $last = $bits[sizeof($bits)-1];
            if (substr($last, -3) == 'jpg') {
                $moreBits = explode('.', $last);
                $info['poster_hash'] = md5($moreBits[0]);
            }
            //$info['imagedata'] = file_get_contents($imagematch[1]);
        }

        // get languages
        if (preg_match('/Language:<\/h4>(.*?)<\/div>/is', $response, $languagematch)) {
            $info['languages'] = str_replace(array(' ', "\n", '5af', "\r"), '', strip_tags($languagematch[1]));
            $info['languages'] = str_replace(array('|', '&nbsp;'), array(',', ''), $info['languages']);
            $info['languages'] = explode(',', $info['languages']);
            $info['language_primary'] = $info['languages'][0];
            $info['language'] = $info['language_primary'];
        }

        // get country
        if (preg_match('/Country:<\/h4>(.*?)<\/div>/is', $response, $countrymatch)) {
            preg_match_all('/<a.*?>(.*?)<\/a>/is', $countrymatch[1], $countrymatches);
            if (sizeof($countrymatches) > 0) {
                $countries = array();
                foreach ($countrymatches[1] as $v) {
                    $countries[] = trim(html_entity_decode($v));
                }
                $info['countries'] = array_unique($countries);
                $info['country'] = $info['countries'][0];
            }
        }

        // runtime
        if (preg_match('/Runtime:<\/h4>(.*?)<\/div>/is', $response, $runtimematch)) {
            $info['runtime'] = str_replace(array(' ', "\n", '5af', "\r"), '', strip_tags($runtimematch[1]));
            $info['runtime'] = str_replace(array('|', '&nbsp;'), array(',', ''), $info['runtime']);
            $info['runtime'] = preg_replace('/\(.*?\)/i', '', $info['runtime']);
            $info['runtime'] = trim($info['runtime']);
        }

        // handle some weird case with us movies not being english as primary
        if (in_array($info['country'], array('USA', 'UK')) and $info['language_primary'] != 'English' and in_array('English', $info['languages'])) {
            $endpointUrl = 'https://query.wikidata.org/sparql';
            $sparqlQuery = '
          SELECT ?item ?itemLabel ?lang ?langLabel WHERE {
            ?item wdt:P31 wd:Q11424.
            ?item wdt:P345 "' . $info['imdbid'] . '".
            OPTIONAL {
              ?item p:P364 ?langStatement.
              ?langStatement ps:P364 ?lang.
            }
            SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
          }
          ';

            $contents = json_decode(file_get_contents($endpointUrl . '?query=' . urlencode($sparqlQuery) . '&format=json'), true);
            if (isset($contents['results']['bindings'])) {
                $language = $contents['results']['bindings'][0]['langLabel']['value'];
                if ($language == 'English') {
                    $info['language_primary'] = 'English';
                    $info['language'] = 'English';

                    $englishKey = array_search('English', $info['languages']);
                    unset($info['languages'][$englishKey]);
                    array_unshift($info['languages'], 'English');
                }
            }
        }

        return $info;
    }
}
