<?php

namespace TRD\Filter\Data;

use TRD\Filter\Filter;
use TRD\Processor\ProcessorResponse;

class ValidData extends Filter
{
    protected $data = null;
  
    public function filter(ProcessorResponse $response)
    {
        if (!isset($this->data['namespace'])) {
            $response->terminate = true;
        }

        if (isset($this->data['namespace']) and !in_array($this->data['namespace'], array('tvmaze','imdb'))) {
            $response->terminate = true;
        }

        return $response;
    }
}
