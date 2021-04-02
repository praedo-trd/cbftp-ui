<?php

namespace TRD\Handler;

use TRD\Model\Prebots;
use TRD\Processor\ProcessorResponse;
use TRD\Utility\ReleaseName;

class PretipHandler extends \TRD\Handler\Handler
{
    public function handle(ProcessorResponse $response)
    {
        $tag = $response->data['tag'];
        $rlsname = $response->data['rlsname'];

        $race = new \TRD\Race\Race($this->container);
        $result = $race->race($tag, $rlsname);

        if ($result->isRace()) {
            $this->container['modelsMemory']['pretips'][$rlsname] = $result;
            $this->container['log']->debug(sprintf(
                'Pretip for tag %s - %s receieved and valid race found',
                $tag,
                $rlsname
            ));
        } else {
            $this->container['log']->debug(sprintf(
                'Pretip for tag %s - %s receieved and no valid race found',
                $tag,
                $rlsname
            ));
        }

        return $response;
    }
}
