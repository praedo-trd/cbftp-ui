<?php

namespace TRD\DupeEngine;

class EngineResult
{
    private $isDupe = false;
    private $data = array();
    private $sources = array();

    public function __construct($isDupe, $sources)
    {
        $this->isDupe = $isDupe;
        $this->sources = $sources;
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function getSourcesAsString()
    {
        $collection = array();
        foreach ($this->sources as $source) {
            $collection[] = $source->getRlsname();
        }
        return implode(',', $collection);
    }

    public function isDupe()
    {
        return $this->isDupe;
    }
}
