<?php

namespace TRD\Handler;

use TRD\Model\Prebots;
use TRD\Processor\ProcessorResponse;
use TRD\Utility\ReleaseName;
use TRD\DataProvider\ReleaseNameDataProvider;

class AddPreHandler extends \TRD\Handler\Handler
{
    public function handle(ProcessorResponse $response)
    {
        $prebots = $this->container['models']['prebots'];
        $settings = $this->container['models']['settings'];

        $db = $this->container['db'];

        $str = $response->data['msg'];

        $rlsname = null;

        foreach ($prebots->getData() as $bot) {
            if (preg_match($bot->channel, $response->data['channel']) and preg_match($bot->bot, $response->data['nick'])) {

                // check the string matches
                if (preg_match($bot->string_match, $str, $addPreMatches)) {
                    $rlsname = $addPreMatches[1];

                    // dont add banned groups
                    if ($settings->exists('banned_groups')) {
                        $bannedGroups = array_map('strtoupper', $settings->get('banned_groups'));
                        $group = ReleaseName::getGroup($rlsname);
                        if (in_array(strtoupper($group), $bannedGroups)) {
                            return $response;
                        }
                    }
                    
                    if ($settings->get('baddir_skip_pre') === true) {
                        $baddir = $settings->get('baddir');
                        if (preg_match($baddir, $rlsname)) {
                            return $response;
                        }
                    }

                    $spacedRelease = ReleaseName::spacify($rlsname);

                    $check = $db->fetchAssoc("SELECT * FROM pre WHERE rlsname = ?", array($spacedRelease));
                    if (empty($check)) {
                        $response->terminate = true;
//                        $response->response =
//                            (new \Malenki\Ansi(' ADDPRE '))->fg('black')->bg('magenta')
//                            . ' ' . $spacedRelease;

                        $now = $db->fetchColumn('SELECT NOW()');

                        $data = array(
                            'rlsname' => $spacedRelease
                            , 'created' => $now
                        );

                        if (preg_match('/[\._](S\d+|[AU]?HDTV|PDTV|DSR|WEB[\._]|WEBRIP)/i', $rlsname)) {
                            $dataProvider = new ReleaseNameDataProvider($this->container);
                            $data['dupe_k'] = ReleaseName::getName($rlsname);
                            $data['dupe_season_episode'] = $dataProvider->extractSeason($rlsname) . '_' . $dataProvider->extractEpisode($rlsname);
                            $data['dupe_resolution'] = $dataProvider->extractResolution($rlsname);
                            $data['dupe_source'] = $dataProvider->extractTVSource($rlsname);
                            $data['dupe_codec'] = $dataProvider->extractCodec($rlsname);
                        }

                        $db->insert('pre', $data);
                    }
                    //.(new \Malenki\Ansi(' '.$section.' '))->fg('black')->bg('gray')
                    //.' ['.implode(',', $sitesInChain).'] '.$rlsname;
                }
            }
        }

        return $response;
    }
}
