<?php

namespace Bundle\LichessBundle;

class Stack
{
    /**
     * Events in the stack
     *
     * @var array
     */
    protected $events = array();
    
    /**
     * Get events
     * @return array
     */
    public function getEvents()
    {
      return $this->events;
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

    public function add(array $event)
    {
        $this->events[] = $event;
    }

    public function reset()
    {
        $this->events = array();
    }
}
