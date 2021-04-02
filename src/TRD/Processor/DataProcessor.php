<?php

namespace TRD\Processor;

use TRD\Processor\Processor;

class DataProcessor extends Processor
{
    protected $str = null;

    public function setCommand($str)
    {
        $this->str = trim($str);

        $bits = explode(' ', $str);

        if (sizeof($bits) >= 3) {
            $this->data = array(
                'namespace' => $bits[0], 'key' => $bits[1], 'id' => $bits[2],
            );
        }
    }
}
