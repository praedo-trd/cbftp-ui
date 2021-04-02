<?php

namespace TRD\Processor;

class ProcessorResponseCommand
{
    private $data = array();
    private $command = null;
    private $consoleOutput = null;
    private $setConsoleOutput = null;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

    public function setConsoleOutput($str)
    {
        $this->setConsoleOutput = $str;
    }

    public function setDataArray($arr)
    {
        $this->data = $arr;
    }

    public function setData($k, $v)
    {
        $this->data["$k"] = $v;
    }

    public function getData($k)
    {
        return $this->data["$k"];
    }
}
