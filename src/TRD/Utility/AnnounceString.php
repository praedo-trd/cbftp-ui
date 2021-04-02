<?php

namespace TRD\Utility;

class AnnounceString
{
    public static function isAnnounceString($siteName, $string, $matchString)
    {
        $matchRegex = '[\s]?' .
            preg_replace(
                '/[\s]+/',
                '\s+',
                preg_quote($matchString, '/')
            )
            . '[\s]?';

        $lookup = explode('|', '&section|&rlsname|&release|&user|&group|&altbookmark|&folder|&size|&date|&multiplier|&reason|&nuker|&time|&other');
        $newRegex = $matchRegex;
        foreach ($lookup as $item) {
            $newRegex = preg_replace("/$item/i", '([^\s]+)', $newRegex);
        }

        $newRegex = '/' . $newRegex . '/i';

        $test = preg_match($newRegex, $string, $matches);
        if ($test === false) {
            var_dump($siteName, $string, $matchString, $matchRegex, $newRegex);
        }
        if ($test) {
            return $matches;
        }
        return false;
    }

    public static function generateRegexAnnounceString($matchString)
    {
    }
}
