<?php

namespace TRD\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The cache.mutated event is dispatched each time the cache is either created
 * or updated in the system.
 */
class CacheMutatedEvent extends Event
{
    const NAME = 'cache.mutated';

    protected $namespace;
    protected $key;
    protected $before;
    protected $after;

    public function __construct($namespace, $key, $before, $after)
    {
        $this->namespace = $namespace;
        $this->key = $key;
        $this->before = $before;
        $this->after = $after;
    }
    
    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getBefore()
    {
        return $this->before;
    }

    public function getAfter()
    {
        return $this->after;
    }
}
