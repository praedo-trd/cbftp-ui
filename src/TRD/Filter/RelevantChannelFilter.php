<?php

namespace TRD\Filter;

use TRD\Filter\SiteFilter;
use TRD\Filter\PrebotFilter;

use TRD\Processor\ProcessorResponse;

class RelevantChannelFilter extends \TRD\Filter\Filter
{
    public function filter(ProcessorResponse $response)
    {
        $settings = $this->container['models']['settings'];

        $siteFilter = new SiteFilter($this->container);
        $siteFilterResponse = $siteFilter->filter(clone $response);

        $prebotFilter = new PrebotFilter($this->container);
        $prebotFilterResponse = $prebotFilter->filter(clone $response);

        $isDataChannel = false;
        if ($response->data['channel'] == $settings->get('data_exchange_channel')) {
            $isDataChannel = true;
        }

        if ($siteFilterResponse->terminate == true and $prebotFilterResponse->terminate == true and $isDataChannel === false) {
            $response->terminate = true;
        }

        if (isset($siteFilterResponse->data['src']) and !empty($siteFilterResponse->data['src'])) {
            $response->data['src'] = $siteFilterResponse->data['src'];
            $this->container['log']->debug(sprintf(
                'Channel %s and Bot %s matched site %s',
                $response->data['channel'],
                $response->data['nick'],
                $siteFilterResponse->data['src']
            ));
        }

        return $response;
    }
}
