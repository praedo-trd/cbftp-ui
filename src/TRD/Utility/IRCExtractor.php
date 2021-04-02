<?php

namespace TRD\Utility;

class IRCExtractor
{
    public static function extract($strings, $checkPrefixes, $target)
    {
        foreach ($checkPrefixes as $prefix) {
            if (!isset($strings->$prefix)) {
                continue;
            }

            if (isset($strings->{$prefix . '-isregex'}) and $strings->{$prefix . '-isregex'} == 1 and preg_match($strings->$prefix, $target, $matches)) {
                $ret = array(
                  'rlsname' => $matches[$strings->{$prefix . '-rls'}]
                  ,'section' => null
                );
                if (!empty($strings->{$prefix . '-section'}) and isset($matches[$strings->{$prefix . '-section'}])) {
                    $ret['section'] = $matches[$strings->{$prefix . '-section'}];
                }

                return $ret;
            } else {
                $announceString = AnnounceString::isAnnounceString(null, $target, $strings->$prefix);
                if ($announceString !== false && key_exists($strings->{$prefix . '-rls'}, $announceString)) {
                    $ret = array(
                      'rlsname' => $announceString[$strings->{$prefix . '-rls'}]
                      ,'section' => null
                    );
                    if (!empty($strings->{$prefix . '-section'}) and isset($announceString[$strings->{$prefix . '-section'}])) {
                        $ret['section'] = $announceString[$strings->{$prefix . '-section'}];
                    }
                    return $ret;
                }
            }
        }
        return array(
          'section' => null,
          'rlsname' => null
      );
    }
}
