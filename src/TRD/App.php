<?php

namespace TRD;

use TRD\Processor\IRCProcessor;
use TRD\Processor\PretipProcessor;
use TRD\Processor\RaceNotificationProcessor;
use TRD\Processor\SimulationProcessor;
use TRD\Utility\ByteArray;
use TRD\Utility\ConsoleDebug;

class App
{
    public $container = null;
    private $server = null;
    private $buffer = null;

    public function __construct(&$container)
    {
        $this->container =& $container;
        $this->buffer = new ByteArray(null);
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function reply($clients, $msg)
    {
        if ($this->server !== null) {
            $conn_list = $this->server->connection_list(0, 10);
            foreach ($conn_list as $fd) {
                $this->server->send($fd, $msg . "\n");
            }
        } else {
            foreach ($clients as $conn) {
                $conn->write($msg . "\n");
            }
        }
    }

    public function handleMessage($clients, $msg)
    {
        // split up into tokens
        $bits = explode(' ', trim($msg));
        $command = implode(' ', array_slice($bits, 1));

        switch ($bits[0]) {
          case 'IRC':
              $processor = new IRCProcessor();
              $processor->setCommand($command);

              // register filters
              $processor->addFilter(new \TRD\Filter\IncomingStringFilter($this->container));
              $processor->addFilter(new \TRD\Filter\RelevantChannelFilter($this->container));

              // register handlers
              $processor->addHandler(new \TRD\Handler\NewPreHandler($this->container));
              $processor->addHandler(new \TRD\Handler\NewRaceHandler($this->container));
              $processor->addHandler(new \TRD\Handler\EndRaceHandler($this->container));
              $processor->addHandler(new \TRD\Handler\AddPreHandler($this->container));
              $processor->addHandler(new \TRD\Handler\NewDataHandler($this->container));

              // process everything
              $response = $processor->process();

              // if we have no response, leave
              if ($response === null) {
                  return;
              }

              if ($response->command !== null) {
                  $this->reply($clients, $response->toJSON());
              }

          break;

          case 'APPROVE':

              $processor = new \TRD\Processor\ApprovalProcessor();
              $processor->setCommand($command);
              $processor->addHandler(new \TRD\Handler\ApprovalHandler($this->container));

              $response = $processor->process();
              if ($response === null) {
                  return;
              }

              if (isset($response->response) and !empty($response->response)) {
                  $this->reply($clients, $response->toJSON());
              }

          break;

          case 'SIMULATE':

              $processor = new SimulationProcessor();
              $processor->setCommand($command);

              $processor->addHandler(new \TRD\Handler\SimulationHandler($this->container));

              $response = $processor->process();
              if ($response == null) {
                  return;
              }

              if (isset($response->response) and !empty($response->response)) {
                  $this->reply($clients, $response->toJSON());
              }

          break;

          case 'PRETIP':

              $processor = new PretipProcessor();
              $processor->setCommand($command);
              $processor->addHandler(new \TRD\Handler\PretipHandler($this->container));

              $response = $processor->process();
              if ($response == null) {
                  return;
              }

              if (isset($response->response) and !empty($response->response)) {
                  $this->reply($clients, $response->toJSON());
              }
          break;

          case 'RACED':
          case 'TRADED':

            $processor = new RaceNotificationProcessor();
            $processor->setCommand($command);

            $processor->addHandler(new \TRD\Handler\RaceNotificationHandler($this->container));
            $response = $processor->process();

            if ($response === null) {
                return;
            }

          break;

          case 'INFO':

              switch ($bits[1]) {
                  case 'AFFILS':

                      //ConsoleDebug::debug("hi");

                  break;
              }

          break;

          case 'DATA':

      //                $processor = new DataProcessor();
      //                $processor->setCommand($command);
      //
      //                $processor->addFilter(new \TRD\Filter\Data\ValidData($this->container));

          break;
      }
    }

    public function process($clients, $str)
    {
        $this->buffer->append($str);
        
        $messages = [];
        while ($msg = $this->buffer->pickUntilByte("\n")) {
            $this->handleMessage($clients, $msg);
        }
      
        // $messages = [];
        // $str = trim($str);
        // $messages = explode("\n", $str);
        // foreach ($messages as $msg) {
        //     $this->handleMessage($clients, $msg);
        // }

        // check irc message queue
        $message = $this->container['db']->fetchAssoc("
          SELECT * FROM irc_message_queue WHERE processed = 0 LIMIT 1
        ");
        if (!empty($message)) {
            $settings = $this->container['models']['settings'];
            $this->reply($clients, 'IRCREPLY ' . $settings->get('data_exchange_channel') . ' ' . $message['message']);
            $this->container['db']->update('irc_message_queue', array('processed' => 1), array('id' => $message['id']));
        }

        // refresh model caches
        foreach ($this->container['models'] as $model) {
            if (method_exists($model, 'refresh')) {
                $model->refresh();
            }
        }
    }
}
