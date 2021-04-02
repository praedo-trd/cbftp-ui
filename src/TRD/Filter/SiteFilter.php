<?php

namespace TRD\Filter;

use TRD\Processor\ProcessorResponse;
use TRD\Model\Sites;

class SiteFilter extends \TRD\Filter\Filter
{
    public function filter(ProcessorResponse $response)
    {
        $sites = $this->container['models']['sites'];

        foreach ($sites->getData() as $siteName => $siteInfo) {
            if (!$siteInfo->enabled) {
                continue;
            }

            $channel = $siteInfo->irc->channel;
            $bot = $siteInfo->irc->bot;

            $channelMatch = preg_match($channel, $response->data['channel']);
            $botMatch = preg_match($bot, $response->data['nick']);

            if ($channelMatch and $botMatch) {
                $response->data['src'] = $siteName;
                return $response;
            }
        }

        $response->terminate = true;

        return $response;
    }
}
