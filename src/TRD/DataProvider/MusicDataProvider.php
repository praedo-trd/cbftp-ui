<?php

namespace TRD\DataProvider;

use TRD\Utility\ReleaseName;

class MusicDataProvider extends \TRD\DataProvider\DataProvider
{
    protected $namespace = 'music';

    public function getDefaults($existing = array())
    {
        return array_merge($existing, array(
                    'language' => null,
                    'source' => null,
                    'year' => null,
                    'disk_count' => null,
        ));
    }

    public static function getDeprecated()
    {
        return [];
    }

    public function lookup($rlsname, $forceRefresh = false)
    {
        $info = $this->getDefaults();

        $info['language'] = $this->extractLanguage($rlsname);
        $info['source'] = $this->extractSource($rlsname);
        $info['year'] = $this->extractYear($rlsname);
        $info['disk_count'] = $this->extractDiskCount($rlsname);

        return new DataProviderResponse(true, $info);
    }

    public function extractLanguage($rlsname)
    {
        $language = null;
        if (preg_match('/\-(AA|AB|AF|AL|AM|AR|AS|AY|AT|AZ|BA|BE|BG|BH|BI|BN|BO|BR|BY|CA|CG|CH|CN|CO|CV|CS|CY|CZ|DA|DE|DD|DK|DZ|EE|EH|EL|EN|EO|ES|ET|EU|FA|FI|FJ|FO|FR|FY|GA|GD|GL|GN|GU|GR|HA|HE|HI|HL|HR|HU|HY|HT|IA|IE|IK|IL|IN|IS|IT|IW|JA|JI|JP|JW|KA|KK|KL|KM|KN|KO|KR|KS|KU|KY|LA|LB|LN|LO|LT|LV|LU|MA|MG|MI|MK|ML|MN|MO|MR|MS|MT|MY|MX|NA|NE|NL|NO|OC|OM|OR|PA|PH|PL|PS|PT|QU|RE|RM|RN|RS|RO|RU|RW|SA|SD|SE|SG|SH|SI|SK|SL|SM|SN|SO|SP|SQ|SR|SS|ST|SU|SY|SV|SW|TA|TE|TG|TH|TI|TK|TL|TN|TO|TR|TS|TT|TW|UA|UR|UZ|VI|VO|WO|XH|YO|YU|ZA|ZH|ZU|ZW|CAT|CRO|DUTCH|SLO|SYR|ESP|CPOP|KPOP)\-/i', $rlsname, $matches)) {
            $language = strtoupper($matches[1]);
        } else {
            $language = 'English';
        }
        return $language;
    }

    public function extractSource($rlsname)
    {
        $source = 'CD';
        if (preg_match('/\-(BD|BDR|BLURAY|HDDVD|HDDVDR)\-/i', $rlsname, $matches)) {
            $source = 'Bluray';
        } elseif (preg_match('/\-(CDA|SACD|BONUS_CD|MCD|CDR|CD|CDS|CDM|CDEP|CDRS|CDREP)\-/i', $rlsname, $matches)) {
            $source = 'CD';
        } elseif (preg_match('/\-(DVD|DVDR|DVDA|DVDS)\-/i', $rlsname, $matches)) {
            $source = 'DVD';
        } elseif (preg_match('/\-(VINYL|VLS|LP|MLP|EP|INCH_VINYL)\-/i', $rlsname, $matches)) {
            $source = 'Vinyl';
        } elseif (preg_match('/\-(TAPE|K7)\-/i', $rlsname, $matches)) {
            $source = 'Tape';
        } elseif (preg_match('/\-(AM|AUD|CABLE|DAB|DAT|DVBC|DVBS|DVBT|FM|LINE|MD|SAT|SBD|RADIO|FM|LIVE|STREAM)\-/i', $rlsname, $matches)) {
            $source = 'Live';
        } elseif (preg_match('/\-WEB\-/i', $rlsname, $matches)) {
            $source = 'WEB';
        } elseif (preg_match('/\-FLASH\-/i', $rlsname, $matches)) {
            $source = 'Flash';
        } elseif (preg_match('/\-(VHS|HOME|HOMEMADE|BOOTLEG|FREE)\-/i', $rlsname, $matches)) {
            $source = 'Other';
        } else {
            $source = 'CD';
        }
        return $source;
    }


    public function extractYear($rlsname)
    {
        $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));
        $year = (int)$now->format('Y');
        if (preg_match('/-((19|20)[0-9]{2})-/i', $rlsname, $matches)) {
            $year = (int)$matches[1];
        }
        return $year;
    }

    public function extractDiskCount($rlsname)
    {
        $diskCount = 1;
        if (preg_match('/-([0-9]*)(CDA|SACD|BONUS_CD|MCD|CDR|CD|CDS|CDM|CDEP|CDRS|CDREP|DVD|DVDR|DVDA|DVDS|VINYL|VLS|LP|MLP|EP|INCH_VINYLTAPE|K7)-/i', $rlsname, $matches)) {
            if ($matches[1] != '') {
                $diskCount = (int)$matches[1];
            }
        }
        return $diskCount;
    }
}
