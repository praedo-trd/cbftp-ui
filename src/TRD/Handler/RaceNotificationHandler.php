<?php

namespace TRD\Handler;

use TRD\DataProvider\IMDBDataProvider;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\Handler\Handler;
use TRD\Processor\ProcessorResponse;

class RaceNotificationHandler extends Handler
{
    public function handle(ProcessorResponse $response)
    {
        $this->container['db']->update('race', array(
          'started' => 1
        ), array(
          'bookmark' => $response->data['tag']
          ,'rlsname' => $response->data['rlsname']
        ));

        return $response;
    }
}
