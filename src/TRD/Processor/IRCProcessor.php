<?php

namespace TRD\Processor;

use TRD\Processor\Processor;

class IRCProcessor extends Processor
{
    public function setCommand($str)
    {
        $this->str = trim($str);

        $bits = explode(' ', $str);

        if (sizeof($bits) >= 3) {
            $this->data = array(
                'channel' => $bits[0], 'nick' => $bits[1], 'msg' => implode(' ', array_slice($bits, 2)),
            );
        }
    }
}
