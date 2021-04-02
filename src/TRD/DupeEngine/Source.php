<?php

namespace TRD\DupeEngine;

use TRD\DataProvider\ReleaseNameDataProvider;

class Source
{
    private $rlsname = null;
    private $score = 0;
    private $fields = array();

    public function __construct($rlsname)
    {
        $this->rlsname = $rlsname;
        $this->fields = ReleaseNameDataProvider::lookupStatic($rlsname);
        $this->score = Engine::getRepeatHierarchy(ReleaseNameDataProvider::extractRepeatExtras($rlsname));
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getRlsname()
    {
        return $this->rlsname;
    }

    public function isInternal()
    {
        return $this->fields['internal'] === true;
    }

    public function getGroup()
    {
        // split by space as we store pre data with spaces :/
        $bits = explode(' ', $this->rlsname);
        return end($bits);
    }

    public function getFields()
    {
        return $this->fields;
    }
}
