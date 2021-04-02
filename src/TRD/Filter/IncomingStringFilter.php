<?php

namespace TRD\Filter;

use TRD\Processor\ProcessorResponse;

class IncomingStringFilter extends \TRD\Filter\Filter
{
    public function filter(ProcessorResponse $response)
    {
        $msg = $response->data['msg'];

        if (substr($response->data['channel'], 0, 1) != '#') {
            $response->terminate = true;
            //$response->response = 'FUCK OFF';
        }

        return $response;
    }
}
