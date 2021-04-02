<?php

namespace TRD\Filter;

use TRD\Processor\ProcessorResponse;

abstract class Filter
{
    protected $container = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    abstract public function filter(ProcessorResponse $response);
}
