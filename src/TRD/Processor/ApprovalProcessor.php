<?php

namespace TRD\Processor;

use TRD\Processor\Processor;

class ApprovalProcessor extends Processor
{
    protected $str = null;

    /*
    Format: APPROVE <tag> <type> <pattern> <chain>
    */
    public function setCommand($str)
    {
        $this->str = trim($str);

        $bits = explode(' ', $str);

        if (sizeof($bits) == 4) {
            $this->data = array(
                'tag' => $bits[0]
                ,'type' => $bits[1]
                ,'pattern' => $bits[2]
                ,'chain' => $bits[3]
            );
        }
    }
}
