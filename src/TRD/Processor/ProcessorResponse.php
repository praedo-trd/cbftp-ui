<?php

namespace TRD\Processor;

class ProcessorResponse
{
    public $terminate;
    public $str;
    public $data;
    public $response;
    public $command = null;
    public $replyChannel = null;
    private $metaData = array();

    public function __construct($terminate, $str, $data = array())
    {
        $this->terminate = $terminate;
        $this->str = $str;
        $this->data = $data;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

    public function setMetaData($data)
    {
        $this->metaData = $data;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function toLegacyString()
    {
    }

    public function toJSON()
    {
        $cmd = $this->command->getCommand();
        switch ($cmd) {
        case 'TRADE':
        case 'APPROVED':

          return json_encode(array(
            'command' => $cmd,
            'tag' => $this->command->getData('bookmark'),
            'chain' => $this->command->getData('chain'),
            'affilSites' => $this->command->getData('affilSites'),
            'rlsname' => $this->command->getData('rlsname'),
            'data' => $this->getMetaData()->all()
          ));

        case 'IRCREPLY':

          return json_encode(array(
            'command' => $cmd
            ,'msg' => $this->command->getData('msg')
            ,'channel' => $this->command->getData('channel')
          ));

        case 'RACECOMPLETESTATUS':
          return json_encode(array(
            'command' => $cmd,
            'rlsname' => $this->command->getData('rlsname'),
            'chain_complete' => $this->command->getData('chain_complete')
          ));

      }
    }
}
