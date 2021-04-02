<?php

namespace TRD\Race;

class RaceResult
{
    public $tag = null;
    public $rlsname = null;
    
    public $data = array();
    public $dataNamespaced = array();
    public $catastrophes = array();
    public $validSites = array();
    public $invalidSites = array();
    public $invalidSitesOverrides = array();
    public $affilSites = array();
    public $exceptions = array();
    public $chain = array();
    private $start = 0;
    public $end = 0;
    public $dataLookupDuration = 0;
    private $duration = 0;
    public $dupeEngineSources = array();
    public $autotraded = false;

    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function isRace()
    {
        return sizeof($this->validSites) > 1 && sizeof($this->catastrophes) === 0;
    }

    public function endRace()
    {
        sort($this->chain);
        sort($this->validSites);
        $this->end = microtime(true);
        $this->duration = round($this->end - $this->start, 3)*1000;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getDataLookupDuration()
    {
        // TODO: remove this as it fixes an old typo
        if (isset($this->datalLookupDuration)) {
            return $this->datalLookupDuration;
        }
        return $this->dataLookupDuration;
    }
    
    public function hasAffils()
    {
        return sizeof($this->affilSites) > 0;
    }
    
    public function getChainWithoutAffils()
    {
        return array_filter($this->chain, function ($v) {
            return !in_array($v, $this->affilSites);
        });
    }
}
