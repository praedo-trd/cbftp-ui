<?php

namespace TRD\Processor;

use TRD\Processor\ProcessorResponse;

abstract class Processor
{
    protected $handlers = array();
    protected $data = null;
    protected $str = null;

    abstract protected function setCommand($str);

    public function addFilter($filter)
    {
        $this->handlers[] = $filter;
    }

    public function addHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function process(): ?ProcessorResponse
    {
        if (empty($this->str)) {
            var_dump('wat');
            return null;
        }

        $response = null;
        if ($this->data !== null) {
            $response = new ProcessorResponse(false, $this->str, $this->data);

            foreach ($this->handlers as $handler) {
                if ($handler instanceof \TRD\Filter\Filter) {
                    $response = $handler->filter($response);
                } elseif ($handler instanceof \TRD\Handler\Handler) {
                    $response = $handler->handle($response);
                }

                if ($response->terminate) {
                    return $response;
                }
            }
        }

        return $response;
    }
}
