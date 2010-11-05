<?php

namespace Bundle\LichessBundle\Document;

/**
 * @mongodb:EmbeddedDocument
 */
class Stack
{
    const MAX_EVENTS = 20;

    /**
     * Events in the stack
     *
     * @var array
     * @mongodb:Field(type="hash")
     */
    protected $events = array();

    public function hasVersion($version)
    {
        return isset($this->events[$version]);
    }

    public function getVersion()
    {
        end($this->events);

        return key($this->events);
    }

    /**
     * Get events
     * @return array
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
        return $this->events[$version];
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
        $this->events[] = $event;
    }

    public function reset()
    {
        $this->events = array();
    }

    public function rotate()
    {
        if(count($this->events) > static::MAX_EVENTS) {
            $this->events = array_slice($this->events, -static::MAX_EVENTS, null, true);
        }
    }
}
