<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @mongodb:EmbeddedDocument
 */
class Stack
{
    const MAX_EVENTS = 20;

    /**
     * Events in the stack
     *
     * @var Collection
     * @mongodb:Field(type="hash")
     */
    protected $events = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getVersion()
    {
        $this->getEvents()->last();
        return $this->getEvents()->key();
    }

    /**
     * Get events
     * @return Collection
     */
    public function getEvents()
    {
      return $this->events;
    }

    /**
     * Get a version event
     *
     * @return array
     **/
    public function getEvent($version)
    {
        return $this->events->get($version);
    }

    /**
     * Add events to the stack
     *
     * @return null
     **/
    public function addEvents(array $events)
    {
        foreach($events as $event) {
            $this->addEvent($event);
        }
    }

    public function addEvent(array $event)
    {
        $this->getEvents()->add($event);
    }

    public function reset()
    {
        $this->getEvents()->clear();
    }

    public function rotate()
    {
        if($this->getEvents()->count() > static::MAX_EVENTS) {
        }
    }
}
