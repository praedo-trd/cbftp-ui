<?php

namespace TRD\Handler;

use TRD\Processor\ProcessorResponse;

abstract class Handler
{
    protected $container = null;

    public function __construct(&$container)
    {
        $this->container =& $container;
    }

    abstract public function handle(ProcessorResponse $response);
}
