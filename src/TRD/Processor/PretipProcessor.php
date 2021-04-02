<?php

namespace TRD\Processor;

use TRD\Processor\Processor;

class PretipProcessor extends Processor
{
    protected $str = null;

    public function setCommand($str)
    {
        $this->str = trim($str);

        $bits = explode(' ', $str);

        if (sizeof($bits) == 2) {
            $this->data = array(
                'tag' => $bits[0], 'rlsname' => $bits[1],
            );
        }
    }
}
