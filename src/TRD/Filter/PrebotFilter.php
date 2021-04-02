<?php

namespace TRD\Filter;

use TRD\Processor\ProcessorResponse;
use TRD\Model\Prebots;

class PrebotFilter extends \TRD\Filter\Filter
{
    public function filter(ProcessorResponse $response)
    {
        $bots = $this->container['models']['prebots'];

        if (is_array($bots->getData())) {
            foreach ($bots->getData() as $botInfo) {
                $channel = $botInfo->channel;
                $bot = $botInfo->bot;

                // let it through :)
                if (preg_match($channel, $response->data['channel']) and preg_match($bot, $response->data['nick'])) {
                    return $response;
                }
            }
        }

        $response->terminate = true;

        return $response;
    }
}
