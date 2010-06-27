<?php

namespace Bundle\LichessBundle;

class Stack
{
    const MAX_EVENTS = 10;

    /**
     * Events in the stack
     *
     * @var array
     */
    protected $events = array();

    public function __construct()
    {
        $this->rotate();
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
        if(!isset($this->events[$version])) {
            throw new \OutOfBoundsException();
        }
        return $this->events[$version];
    }
    
    /**
     * Set events
     * @param  array
     * @return null
     */
    public function setEvents($events)
    {
      $this->events = $events;
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
