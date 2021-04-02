<?php

namespace TRD\DataProvider;

use TRD\Utility\ReleaseName;
use TRD\DataProvider\DataProviderResponse;

class ReleaseNameDataProvider extends \TRD\DataProvider\DataProvider
{
    const EPISODE_FORM1 = '/[\s._-](S\d+E(\d+)-?E(\d+))[\s._-]/i';
    const EPISODE_FORM2 = '/[\s._-]((?:S\d+)?(?:Episode|E|Part)\.?(\d+))[\s._-]/i';
    const EPISODE_FORM3 = '/[\s._-](\d+x(\d+))[\s._-]/i';
    const EPISODE_FORM4 = '/[\s._-](\d{4}\.(\d{2}\.\d{2}))[\s._-]/i';
  
    protected $namespace = 'rlsname';
    protected static $defaults = array(
      'episode' => null,
      'season' => null,
      'codec' => null,
      'source' => null,
      'resolution' => null,
      'language' => null,
      'multi' => false,
      'internal' => false
    );

    public function getDefaults($existing = array())
    {
        return array_merge($existing, self::$defaults);
    }

    public static function getDefaultsStatic($existing = array())
    {
        return array_merge($existing, self::$defaults);
    }

    public static function getDeprecated()
    {
        return [];
    }

    public function lookup($rlsname, $forceRefresh = false)
    {
        $info = $this->getDefaults();
        $info['episode'] = $this->extractEpisode($rlsname);
        $info['season'] = $this->extractSeason($rlsname);

        // media data
        $info['codec'] = $this->extractCodec($rlsname);
        $info['source'] = $this->extractTVSource($rlsname);
        $info['resolution'] = $this->extractResolution($rlsname);
        $info['range'] = self::extractRange($rlsname);

        // try and get the country
        $info = $this->extractOther($rlsname, $info);

        // dupe stuff
        $info['repeat'] = $this->extractRepeatExtras($rlsname);

        // meta-data
        $info['cleaned'] = ReleaseName::getName($rlsname);
        $info['cleaned_joined'] = implode('.', explode(' ', $info['cleaned']));
        $info['group'] = ReleaseName::getGroup($rlsname);

        return new DataProviderResponse(true, $info);
    }

    public static function lookupStatic($rlsname)
    {
        $info = self::getDefaultsStatic();
        $info['episode'] = self::extractEpisode($rlsname);
        $info['season'] = self::extractSeason($rlsname);

        // media data
        $info['codec'] = self::extractCodec($rlsname);
        $info['source'] = self::extractTVSource($rlsname);
        $info['resolution'] = self::extractResolution($rlsname);
        $info['range'] = self::extractRange($rlsname);

        // try and get the country
        $info = self::extractOther($rlsname, $info);

        // dupe stuff
        $info['repeat'] = self::extractRepeatExtras($rlsname);

        // meta-data
        $info['cleaned'] = ReleaseName::getName($rlsname);
        $info['cleaned_joined'] = implode('.', explode(' ', $info['cleaned']));
        $info['group'] = ReleaseName::getGroup($rlsname);

        return $info;
    }

    private function extractRegion($rlsname)
    {
        // TODO: implement this
    }

    public static function extractOther($rlsname, $otherInfo)
    {
        $episodeToken = self::extractEpisodeToken($rlsname);
        if ($episodeToken !== '') {
            $firstSplit = preg_split('/' . $episodeToken . '[\s_.]/i', $rlsname);
            $cleanedRelease = preg_replace('/-\w+$/i', '', $firstSplit[1]);
            $cleanedRelease = str_ireplace(
                array(
            $otherInfo['codec'], $otherInfo['source'], $otherInfo['resolution']
          ),
                array('','',''),
                $cleanedRelease
            );

            $possibleLanguages = explode('|', strtoupper(ReleaseName::LANGUAGES));

            $finalSplit = preg_split('/[\s_.]+/i', $cleanedRelease);

            // try and get the language now
            foreach ($finalSplit as $tok) {
                $ut = strtoupper($tok);
                if (empty($otherInfo['language']) and in_array($ut, $possibleLanguages)) {
                    $otherInfo['language'] = $tok;
                }
                if ($ut === 'INTERNAL' or $ut == 'INT') {
                    $otherInfo['internal'] = true;
                }
            }
        }

        if (stripos($rlsname, 'MULTI') !== false) {
            $otherInfo['multi'] = true;
        }

        return $otherInfo;
    }

    public static function extractResolution($rlsname)
    {
        $resolution = null;
        if (preg_match('/[\s._-](720P|1080P|1280P|1440P|1920P|2160P|2300P|2700P|2880P)[\s._-]/i', $rlsname, $matches)) {
            $resolution = strtoupper($matches[1]);
        } elseif (preg_match('/[\s._-](UHD\.BLURAY)/i', $rlsname, $matches)) {
            $resolution = '2160P';
        } elseif (preg_match('/[\s._-](COMPLETE\.M?BLURAY)/i', $rlsname, $matches)) {
            $resolution = '1080P';
        }
        return $resolution;
    }

    public static function extractCodec($rlsname)
    {
        $codec = null;
        if (preg_match('/[\s._-]([xh]26[45]|xvid|VP[89])[\s._-]/i', $rlsname, $matches)) {
            $codec = strtoupper($matches[1]);
        }

        return $codec;
    }
    
    public static function extractRange($rlsname)
    {
        $range = null;
        if (preg_match('/[\s._-](HDR)[\s._-]/i', $rlsname, $matches)) {
            $range = strtoupper($matches[1]);
        }

        return $range;
    }

    public static function extractTVSource($rlsname)
    {
        $source = null;
        if (preg_match('/[\s._-]([AU]?HDTV|PDTV|DSR|WEBRIP|WEB)[\s._-]/i', $rlsname, $matches)) {
            $source = strtoupper($matches[1]);
        } elseif (preg_match('/[\s._-]((720p|1080p)\.BLURAY)/i', $rlsname, $matches)) {
            $source = 'BLURAY';
        } elseif (preg_match('/[\s._](UHD\.BLURAY)/i', $rlsname, $matches)) {
            $source = 'UHD.BLURAY';
        } elseif (preg_match('/[\s._-](COMPLETE\.M?BLURAY)/i', $rlsname, $matches)) {
            $source = 'BLURAY';
        } elseif (preg_match('/[\s._-](DVDRIP|BDRIP)/i', $rlsname, $matches)) {
            $source = strtoupper($matches[1]);
        }
        return $source;
    }

    public static function extractEpisode($rlsname)
    {
        if (preg_match(self::EPISODE_FORM1, $rlsname, $matches)) {
            return $matches[2] . $matches[3];
        } elseif (preg_match(self::EPISODE_FORM2, $rlsname, $matches)) {
            return (int)$matches[2];
        } elseif (preg_match(self::EPISODE_FORM3, $rlsname, $matches)) {
            return (int)$matches[2];
        } elseif (preg_match(self::EPISODE_FORM4, $rlsname, $matches)) {
            return $matches[2];
        }
        return '';
    }
    
    public static function extractEpisodeToken($rlsname)
    {
        if (preg_match(self::EPISODE_FORM1, $rlsname, $matches)) {
            return $matches[1];
        } elseif (preg_match(self::EPISODE_FORM2, $rlsname, $matches)) {
            return $matches[1];
        } elseif (preg_match(self::EPISODE_FORM3, $rlsname, $matches)) {
            return $matches[1];
        } elseif (preg_match(self::EPISODE_FORM4, $rlsname, $matches)) {
            return $matches[1];
        }
        return '';
    }

    public static function extractSeason($rlsname)
    {
        $season = '';
        if (preg_match('/[\s\._]S(\d+)?(?:Episode|E)\d+(-?E\d+)?[\s\._]/i', $rlsname, $matches)) {
            $season = (int)$matches[1];
        } elseif (preg_match('/[\s\._](\d+)x\d+[\s\._]/i', $rlsname, $matches)) {
            $season = (int)$matches[1];
        } elseif (preg_match('/[\s\._](\d{4})\.\d{2}\.\d{2}[\s\._]/i', $rlsname, $matches)) {
            $season = (int)$matches[1];
        } elseif (preg_match('/[\s\._]S(\d+)[\s\._]/i', $rlsname, $matches)) {
            $season = (int)$matches[1];
        }
        return $season;
    }

    public static function extractRepeatExtras($rlsname)
    {
        $lookup = array(
        'REAL.PROPER', 'PROPER', 'RERIP', 'REPACK'
      );

        foreach ($lookup as $l) {
            $idx = strpos($rlsname, $l);
            if ($idx !== false && $idx !== -1) {
                return substr($rlsname, $idx, strlen($l));
            }
        }

        return null;
    }
}
