<?php

namespace TRD\DataProvider;

use TRD\Utility\ReleaseName;

class DateTimeDataProvider extends \TRD\DataProvider\DataProvider
{
    protected $namespace = 'datetime';

    public function getDefaults($existing = array())
    {
        return array_merge($existing, array(
            'year' => null,
            'last_year' => null,
            'last_2_years' => null,
            'last_3_years' => null,
            'last_4_years' => null,
            'last_5_years' => null,
            'hour' => null,
            'minute' => null,
            'day_of_week' => null
        ));
    }

    public static function getDeprecated()
    {
        return [];
    }

    public function lookup($rlsname, $forceRefresh = false)
    {
        $info = $this->getDefaults();

        $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));

        $info['this_year'] = (int)$now->format('Y');
        $info['last_year'] = $info['this_year'] - 1;
        $info['last_2_years'] = array($info['this_year'], $info['last_year']);
        $info['last_3_years'] = array($info['this_year'], $info['last_year'], $info['last_year']-1);
        $info['last_4_years'] = array($info['this_year'], $info['last_year'], $info['last_year']-1, $info['last_year']-2);
        $info['last_5_years'] = array($info['this_year'], $info['last_year'], $info['last_year']-1, $info['last_year']-2, $info['last_year']-3);

        $info['hour'] = (int)$now->format('G');
        $info['minute'] = (int)$now->format('i');
        $info['day_of_week'] = (int)$now->format('N');

        return new DataProviderResponse(true, $info);
    }
}
