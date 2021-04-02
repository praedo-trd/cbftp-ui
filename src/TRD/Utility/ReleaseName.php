<?php

namespace TRD\Utility;

class ReleaseName
{
    const COUNTRY_TLDS = 'AC|AD|AE|AF|AG|AI|AL|AM|AN|AO|AQ|AR|AS|AT|AU|AW|AZ|BA|BB|BD|BE|BF|BG|BH|BI|BJ|BM|BN|BO|BR|BS|BT|BV|BW|BY|BZ|CA|CC|CD|CF|CG|CH|CI|CK|CL|CM|CN|CO|CR|CS|CU|CV|CX|CY|CZ|DE|DJ|DK|DM|DO|DZ|EC|EE|EG|EH|ER|ES|ET|EU|FI|FJ|FK|FM|FO|FR|GA|GB|GD|GE|GF|GG|GH|GI|GL|GM|GN|GP|GQ|GR|GS|GT|GU|GW|GY|HK|HM|HN|HR|HT|HU|ID|IE|IL|IM|IN|IO|IQ|IR|IS|IT|JE|JM|JO|JP|KE|KG|KH|KI|KM|KN|KP|KR|KW|KY|KZ|LA|LB|LC|LI|LK|LR|LS|LT|LU|LV|LY|MA|MC|MD|MG|MH|MK|ML|MM|MN|MO|MP|MQ|MR|MS|MT|MU|MV|MW|MX|MY|MZ|NA|NC|NE|NF|NG|NI|NL|NO|NP|NR|NU|NZ|OM|PA|PE|PF|PG|PH|PK|PL|PM|PN|PR|PS|PT|PW|PY|QA|RE|RO|RU|RW|SA|SB|SC|SD|SE|SG|SH|SI|SJ|SK|SL|SM|SN|SO|SR|ST|SU|SV|SY|SZ|TC|TD|TF|TG|TH|TJ|TK|TM|TN|TO|TP|TR|TT|TV|TW|TZ|UA|UG|UK|UM|US|UY|UZ|VA|VC|VE|VG|VI|VN|VU|WF|WS|YE|YT|YU|ZA|ZM|ZR|ZW';

    const LANGUAGES = 'Afar|Abkhaz|Avestan|Afrikaans|Akan|Amharic|Aragonese|Arabic|Assamese|Avaric|Aymara|Azerbaijani|Bashkir|Belarusian|Bulgarian|Bihari|Bislama|Bambara|Bengali|Tibetan|Breton|Bosnian|Catalan|Valencian|Chechen|Chamorro|Corsican|Cree|Czech|Chuvash|Welsh|Danish|German|Divehi|Dhivehi|Maldivian|Dzongkha|Ewe|Greek|English|Esperanto|Spanish|Castilian|Estonian|Basque|Persian|Fula|Fulah|Pulaar|Pular|Finnish|Fijian|Faroese|French|Frisian|Irish|Gaelic|Galician|Gujarati|Manx|Hausa|Hebrew|Hindi|Hiri Motu|Croatian|Haitian|Hungarian|Armenian|Herero|Interlingua|Indonesian|Interlingue|Igbo|Nuosu|Inupiaq|Ido|Icelandic|Italian|Inuktitut|Japanese|Javanese|Georgian|Kongo|Kikuyu|Gikuyu|Kwanyama|Kuanyama|Kazakh|Kalaallisut|Greenlandic|Khmer|Kannada|Korean|Kanuri|Kashmiri|Kurdish|Komi|Cornish|Kirghiz|Kyrgyz|Latin|Luxembourgish|Letzeburgesch|Luganda|Limburgish|Limburgan|Limburger|Lingala|Lao|Lithuanian|Luba-Katanga|Latvian|Malagasy|Marshallese|Maori|Macedonian|Malayalam|Mongolian|Malay|Maltese|Burmese|Nauru|Nepali|Ndonga|Dutch|Norwegian|Nynorsk|Navajo|Navaho|Chichewa|Chewa|Nyanja|Occitan|Ojibwe|Ojibwa|Oromo|Oriya|Ossetian|Ossetic|Panjabi|Punjabi|Pali|Polish|Pashto|Pushto|Portuguese|Quechua|Romansh|Kirundi|Romanian|Moldavian|Moldovan|Russian|Kinyarwanda|Sanskrit|Sardinian|Sindhi|Sami|Sango|Sinhala|Sinhalese|Slovak|Slovene|Samoan|Shona|Somali|Albanian|Serbian|Swati|Sundanese|Swedish|Swahili|Tamil|Telugu|Tajik|Thai|Tigrinya|Turkmen|Tagalog|Tswana|Turkish|Tsonga|Tatar|Twi|Tahitian|Uighur|Uyghur|Ukrainian|Urdu|Uzbek|Venda|Vietnamese|Walloon|Wolof|Xhosa|Yiddish|Yoruba|Zhuang|Chuang|Chinese|Zulu';

    public static function getGroup($rlsname)
    {
        $bits = explode('-', $rlsname);

        return strtoupper($bits[sizeof($bits) - 1]);
    }

    public static function spacify($rlsname)
    {
        return preg_replace('/\s{2,}/i', ' ', preg_replace('/[^\sa-z0-9]+/i', ' ', $rlsname));
    }

    public static function transliterate($str)
    {
        if (!preg_match('/[\x80-\xff]/', $str)) {
            return $str;
        }

        $chars = array(
          // Decompositions for Latin-1 Supplement
          chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
          chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
          chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
          chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
          chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
          chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
          chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
          chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
          chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
          chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
          chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
          chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
          chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
          chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
          chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
          chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
          chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
          chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
          chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
          chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
          chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
          chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
          chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
          chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
          chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
          chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
          chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
          chr(195).chr(191) => 'y',
          // Decompositions for Latin Extended-A
          chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
          chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
          chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
          chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
          chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
          chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
          chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
          chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
          chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
          chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
          chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
          chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
          chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
          chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
          chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
          chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
          chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
          chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
          chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
          chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
          chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
          chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
          chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
          chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
          chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
          chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
          chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
          chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
          chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
          chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
          chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
          chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
          chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
          chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
          chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
          chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
          chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
          chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
          chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
          chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
          chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
          chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
          chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
          chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
          chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
          chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
          chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
          chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
          chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
          chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
          chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
          chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
          chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
          chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
          chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
          chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
          chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
          chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
          chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
          chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
          chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
          chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
          chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
          chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
          );

        $str = strtr($str, $chars);

        return $str;
    }

    public static function getReleaseNameFromDirectory($rlsname)
    {
        if (empty($rlsname) or substr_count($rlsname, '/') == 0) {
            return $rlsname;
        }

        $bits = explode('/', $rlsname);
        return $bits[sizeof($bits)-1];
    }

    /**
     * @param string $name name of content, NOT rlsname
     * @return string|null
     */
    public static function getCountry($name)
    {
        $tokens = explode(' ', self::cleanTitle($name));
        $lastToken = $tokens[sizeof($tokens)-1];

        if (preg_match('/^(' . self::COUNTRY_TLDS . ')$/', $lastToken, $match)) {
            return $match[1];
        }
        return null;
    }

    /**
     * @param string $name Name of content, NOT rlsname
     * @return string|null
     */
    public static function getYear($name)
    {
        $tokens = explode(' ', self::cleanTitle($name));
        $lastToken = $tokens[sizeof($tokens)-1];

        if (preg_match('/(\d{4})/i', $lastToken, $match)) {
            return $match[1];
        }
        return null;
    }

    public static function titleToRlsname($title, $strict = false)
    {

        // remove bad things in brackets
        if (!$strict) {
            $title = preg_replace('/\((Video|TV).*?\)$/i', '', $title);
        }

        $title = str_replace(
            array(
                '&'
                ,'@'
            ),
            array(
                'and'
                ,'at'
            ),
            $title
        );

        // remove bad chars
        $title = preg_replace('/[^a-z-\d\s]+/i', '', $title);

        // remove breaks
        $title = str_replace(' - ', ' ', $title);

        if ($strict) {
            $title = str_replace(' ', '.', trim($title));
        }

        return trim($title);
    }

    public static function getName($rlsname, $strict = false)
    {
        $originalReleaseName = $rlsname;

        // lowercase and trim to clean anything dirty
        $rlsname = trim($rlsname);

        // pop off the group
        $rlsname = str_replace('-'.self::getGroup($rlsname), '', $rlsname);

        // strip definite bad things
        $rlsname = preg_replace(array(
            '/[\._]directors[\._]cut/i'
            , '/[\._](nfo|dir|sub|sample|proof|covers|cover|sync)fix/i'
            , '/[\._]real[\._]proper/i'
            , '/[\._](bdrip|dvdrip)/i'
            , '/[\._](repack)/i'
        ), '', $rlsname);

        // strip by the obvious first!
        if (preg_match('/^(.*?)[\._](720p|1080p|1280p|1440p|1920p|2160p|2300p|2700p|2880p|S\d+E\d+|S\d+D\d+|E\d+|S\d+|Episode[\._]\d+|\d{1,3}x\d{1,3})/i', $rlsname, $matches)) {
            $rlsname = $matches[1];
        }

        // handle the more complicated cases now
        $splitted = preg_split('/[\._](xvid|[hx]26[45]|dvdr)/i', $rlsname);
        if (sizeof($splitted) > 0) {
            $rlsname = $splitted[0];
        }

        $tokens = explode(' ', self::cleanTitle($rlsname));
        $lastToken = $tokens[sizeof($tokens)-1];

//        var_dump($rlsname, $lastToken);die;

        // if the end is a date then strip it off and return
        if (preg_match('/\d{4}\.\d{2}\.\d{2}/i', $rlsname)) {
            return self::cleanTitle(preg_replace('/\.\d{4}\.\d{2}\.\d{2}.*?$/i', '', $rlsname));
        }

        // if the last token is a year after 1900 we can assume it has no shit
        if ((int)$lastToken > 1900 && !$strict) {
            return self::cleanTitle($rlsname);
        }

        // if one token is a year then let's just take everything before it
        // as this covers most cases
        $slicedTokens = array_slice($tokens, 1);
        foreach ($slicedTokens as $k => $tok) {
            if ((int)$tok > 1900) {
                // fix for double year!
                if (isset($tokens[$k+2]) and (int)$tokens[$k+2] > 1900) {
                    return implode(' ', array_slice($tokens, 0, $k+3));
                }
                return implode(' ', array_slice($tokens, 0, $k+2));
            }
        }

        if (in_array(strtolower($lastToken), array(
            'limited', 'dc', 'proper', 'uncut', 'extended', 'chrono'
            , 'extras', 'festival', 'docu', 'stv', 'ntsc', 'pal'
            , 'nordic', 'dutch', 'russian', 'finnish', 'swedish', 'norwegian', 'danish'
            , 'czech', 'german', 'dubbed', 'subbed', 'subpack', 'french', 'spanish', 'portugese'
        ))) {
            return implode(' ', array_slice($tokens, 0, -1));
        }

        if ($strict && preg_match('/^(' . self::COUNTRY_TLDS . ')$/', $lastToken, $wtf)) {
            return implode(' ', array_slice($tokens, 0, -1));
        }

        return self::cleanTitle($rlsname);
    }

    public static function cleanTitle($title)
    {
        return preg_replace('/[\._]/', ' ', $title);
    }

    public static function passesRegexSkiplists($rlsname, $skiplists, $regexes = null)
    {
        foreach ($skiplists as $skiplistItem) {
            if (empty($skiplistItem)) {
                continue;
            }
          
            if ($regexes !== null && substr($skiplistItem, 0, 7) === '[regex.' and substr($skiplistItem, -1) === ']') {
                if (preg_match('/^\[regex\.(.*?)\]$/i', $skiplistItem, $regexMatch)) {
                    $getRegex = $regexes->getSkiplistRegex($regexMatch[1]);
                    if (!empty($getRegex) && preg_match($getRegex, $rlsname)) {
                        return $skiplistItem;
                    }
                }
            } else {
                if (preg_match($skiplistItem, $rlsname)) {
                    return $skiplistItem;
                }
            }
        }
        return true;
    }

    public static function passesRequirements($rlsname, $requirements, $regexes = null)
    {
        $failedRequirements = array();
        if (is_array($requirements)) {
            foreach ($requirements as $requirement) {
                if (empty($requirement)) {
                    continue;
                }
            
                if ($regexes !== null && substr($requirement, 0, 7) === '[regex.' and substr($requirement, -1) === ']') {
                    if (preg_match('/^\[regex\.(.*?)\]$/i', $requirement, $regexMatch)) {
                        $getRegex = $regexes->getSkiplistRegex($regexMatch[1]);
                        if (!empty($getRegex) && !preg_match($getRegex, $rlsname)) {
                            $failedRequirements[] = $requirement;
                        }
                    }
                } else {
                    if (!preg_match($requirement, $rlsname)) {
                        $failedRequirements[] = $requirement;
                    }
                }
            }
            if (sizeof($failedRequirements) > 0) {
                return $failedRequirements;
            }
        }
        return true;
    }
}
