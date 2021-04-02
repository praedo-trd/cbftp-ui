<?php

namespace TRD\DupeEngine;

use TRD\DupeEngine\EngineResult;
use TRD\DataProvider\ReleaseNameDataProvider;
use TRD\Utility\ReleaseName;

class Engine
{
    private $sources = array();
    private $filterRegex = array();
    private $container = null;

    public function __construct($sources)
    {
        $this->sources = $sources;
    }

    public function addFilterRegex($regex)
    {
        $this->filterRegex[] = $regex;
    }

    public function addSource($source)
    {
        $this->sources[] = $source;
    }

    public static function getRepeatHierarchy($dupePart)
    {
        $lookup = array(
          'REAL.PROPER', 'PROPER', 'REAL.RERIP', 'RERIP', 'REAL.REPACK', 'REPACK'
        );

        $idx = array_search($dupePart, $lookup);
        if ($idx !== false) {
            return sizeof($lookup) - $idx;
        }
        return 0;
    }

    public static function getValidDupeRuleStrings()
    {
        return array('source.firstWins', 'source.priority');
    }

    private function filterSources($rlsname, $fields)
    {
        $sources = $this->sources;
        $releaseScore = self::getRepeatHierarchy($fields['repeat']);

        // TODO: lots of looping over the sources here - could be one loop

        // remove internals + existing rlsname + multis
        foreach ($sources as $k => $source) {
            $sourceFields = $source->getFields();
            
            // remove internals
            if ($sourceFields['internal'] === true) {
                unset($sources[$k]);
            }
            
            // remove multi
            if ($sourceFields['multi'] === true) {
                unset($sources[$k]);
            }
            
            // remove rlsname that we're checking against
            if ($source->getRlsname() === $rlsname || $source->getRlsname() === ReleaseName::spacify($rlsname)) {
                unset($sources[$k]);
            }
        }

        // regex source filters
        if (sizeof($this->filterRegex) > 0) {
            foreach ($sources as $k => $source) {
                foreach ($this->filterRegex as $re) {
                    if (preg_match($re, $source->getRlsname())) {
                        unset($sources["$k"]);
                    }
                }
            }
        }

        // filter other language sources out as they cannot be dupe
        foreach ($sources as $k => $source) {
            $sourceFields = $source->getFields();
            if ($sourceFields['language'] != $fields['language']) {
                unset($sources["$k"]);
            }
        }

        // handle the score business
        foreach ($sources as $k => $source) {
            $sourceFields = $source->getFields();
            if ($source->getScore() < $releaseScore) {
                unset($sources["$k"]);
            }
        }


        return $sources;
    }

    public function isDupe($rlsname, $rules = null, $extra = array())
    {
        $releaseFields = ReleaseNameDataProvider::lookupStatic($rlsname);

        // filter sources
        $sources = $this->filterSources($rlsname, $releaseFields);
        
        // internal is never a dupe
        if ($releaseFields['internal'] === true) {
            return new EngineResult(false, $sources);
        }
        
        

        // no sources, no dupes
        if (sizeof($sources) === 0) {
            return new EngineResult(false, $sources);
        }

        // if we have no rules it's never a dupe
        if (empty($rules) or !in_array($rules, self::getValidDupeRuleStrings())) {
            return new EngineResult(false, $sources);
        }

        // first wins and a source? it's a dupe
        if ($rules === 'source.firstWins' and sizeof($sources) > 0) {
            return new EngineResult(true, $sources);
        }

        // figure out the priority stuff
        if ($rules === 'source.priority' and isset($extra['priority'])) {
            $priorityList = array_map('strtoupper', explode(',', $extra['priority']));
            if (sizeof($priorityList) > 1) {
                $releaseSource = strtoupper($releaseFields['source']);
                $releasePosition = array_search($releaseSource, $priorityList);
                if ($releasePosition !== false) {
                    foreach ($sources as $source) {
                        $sourcePosition = array_search($source->getFields()['source'], $priorityList);
                        if ($sourcePosition !== false) {
                            if ($releasePosition <= $sourcePosition) {
                                return new EngineResult(true, $sources);
                            }
                        }
                    }
                }
            }
        }

        return new EngineResult(false, $sources);
    }
}
