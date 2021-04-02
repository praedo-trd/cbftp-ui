<?php

namespace TRD\Handler;

use TRD\DataProvider\IMDBDataProvider;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\Handler\Handler;
use TRD\Processor\ProcessorResponse;

class ApprovalHandler extends Handler
{
    public function handle(ProcessorResponse $response)
    {

        // TODO: validate the chain
        $sites = $this->container['models']['sites'];

        // TODO: check the rule doesn't exist already

        // expiry
        $expires = new \DateTime('now +1day', new \DateTimeZone($_ENV['APP_TIMEZONE']));

        // insert the rule
        $this->container['db']->insert('approved', array(
          'bookmark' => $response->data['tag']
          ,'chain' => $response->data['chain']
          ,'pattern' => $response->data['pattern']
          ,'type' => $response->data['type']
          ,'maxlimit' => 24
          ,'expires' => $expires->format('Y-m-d H:i:s')
        ));

        // TODO: handle response communication better
        $response->response = '1';

        $command = new \TRD\Processor\ProcessorResponseCommand('APPROVALRESULT');
        $response->setCommand($command);

        return $response;
    }
}
