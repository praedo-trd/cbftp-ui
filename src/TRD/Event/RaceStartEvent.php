<?php

namespace TRD\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The cache.mutated event is dispatched each time the cache is either created
 * or updated in the system.
 */
class RaceStartEvent extends Event
{
    const NAME = 'race.start';

    protected $raceResult;

    public function __construct(\TRD\Race\RaceResult $raceResult)
    {
        $this->raceResult = $raceResult;
    }

    public function getRaceResult()
    {
        return $this->raceResult;
    }
}
