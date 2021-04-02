<?php

namespace TRD\Processor;

use TRD\Processor\Processor;

class RaceNotificationProcessor extends Processor
{
    protected $str = null;

    public function setCommand($str)
    {
        $this->str = trim($str);

        $bits = explode(' ', $str);

        if (sizeof($bits) == 3) {
            $this->data = array(
                'tag' => $bits[0], 'rlsname' => $bits[1], 'chain' => $bits[2]
            );
        }
    }
}
