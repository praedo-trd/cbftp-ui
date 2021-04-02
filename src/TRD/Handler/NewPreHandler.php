<?php

namespace TRD\Handler;

use TRD\Processor\ProcessorResponse;
use TRD\Model\Sites;
use TRD\Utility\ConsoleDebug;
use TRD\Utility\ReleaseName;
use TRD\Utility\AnnounceString;
use TRD\DataProvider\ReleaseNameDataProvider;

class NewPreHandler extends \TRD\Handler\Handler
{
    public function handle(ProcessorResponse $response)
    {
        if (!isset($response->data['src'])) {
            return $response;
        }

        $db = $this->container['db'];

        $str = $response->data['msg'];

        // check each sites strings
        $sites = $this->container['models']['sites'];

        $site = $response->data['src'];
        $irc = $sites->getData()->$site->irc->strings;

        $extraction = \TRD\Utility\IRCExtractor::extract($irc, array('prestring'), $str);
        $section = $extraction['section'];
        $rlsname = $extraction['rlsname'];

        // we have a match
        if ($section !== null and $rlsname !== null) {
            $this->container['log']->debug(sprintf(
                'Site %s found pre string for section %s with rlsname %s',
                $site,
                $section,
                $rlsname
            ));

            // add a new affil if we come across it
            if ($sites->addAffil($response->data['src'], ReleaseName::getGroup($rlsname))) {
                ConsoleDebug::incoming((new \Malenki\Ansi(' INFO '))->fg('black')->bg('blue')
                    .' New affil <'.ReleaseName::getGroup($rlsname).'> added for <'.$response->data['src'].'>');
            }

            $spacedRelease = ReleaseName::spacify($rlsname);

            // add to pre db
            $check = $db->fetchAssoc("SELECT id FROM pre WHERE rlsname = ?", array($spacedRelease));
            if (empty($check)) {
                $now = $db->fetchColumn('SELECT NOW()');
                $data = array(
                    'rlsname' => $spacedRelease
                    , 'created' => $now
                );


                if (preg_match('/[\._](S\d+|[AU]?HDTV|PDTV|DSR|WEB[\._]|WEBRIP).*?([xh]26[45]|xvid)/i', $rlsname)) {
                    $dataProvider = new ReleaseNameDataProvider($this->container);
                    $data['dupe_k'] = ReleaseName::getName($rlsname);
                    $data['dupe_season_episode'] = $dataProvider->extractSeason($rlsname) . '_' . $dataProvider->extractEpisode($rlsname);
                    $data['dupe_resolution'] = $dataProvider->extractResolution($rlsname);
                    $data['dupe_source'] = $dataProvider->extractTVSource($rlsname);
                    $data['dupe_codec'] = $dataProvider->extractCodec($rlsname);
                }

                $db->insert('pre', $data);

                ConsoleDebug::incoming((new \Malenki\Ansi(' PRE '))->fg('black')->bg('magenta')
                .(new \Malenki\Ansi(' '.$site.' '))->fg('black')->bg('gray')
                    . ' ' . $spacedRelease);
            }
        }

        return $response;
    }
}
