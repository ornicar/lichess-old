<?php

namespace Bundle\LichessBundle\Model;

abstract class Stack {

    const MAX_EVENTS = 8;

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
        $events = array();
        foreach($this->events as $version => $event) {
            $events[$version] = $this->decodeEvent($event);
        }

        return $events;
    }

    public function getEncodedEvents()
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
        return $this->decodeEvent($this->events[$version]);
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
        $this->events[] = $this->encodeEvent($event);
        $this->rotate();
    }

    public function reset()
    {
        $this->events = array();
    }

    public function rotate()
    {
        if(count($this->events) > $this->getMaxEvents()) {
            $this->events = array_slice($this->events, -$this->getMaxEvents(), null, true);
        }
    }

    public function getNbEvents()
    {
        return count($this->events);
    }

    public function getMaxEvents()
    {
        return self::MAX_EVENTS;
    }

    protected function encodeEvent(array $event)
    {
        if('possible_moves' === $event['type']) {
            if(empty($event['possible_moves'])) {
                $possibleMoves = null;
            } else {
                $possibleMoves = array();
                foreach($event['possible_moves'] as $from => $tos) {
                    $possibleMoves[$from] = implode(' ', $tos);
                }
            }
            $event = array(
                'pm' => $possibleMoves
            );
        } elseif('move' === $event['type']) {
            $event = array(
                'm' => $event['from'].' '.$event['to']
            );
        }

        return $event;
    }

    protected function decodeEvent(array $event)
    {
        if(array_key_exists('pm', $event)) {
            if(empty($event['pm'])) {
                $possibleMoves = null;
            } else {
                $possibleMoves = array();
                foreach($event['pm'] as $from => $tos) {
                    $possibleMoves[$from] = explode(' ', $tos);
                }
            }
            $event = array(
                'type' => 'possible_moves',
                'possible_moves' => $possibleMoves
            );
        } elseif(isset($event['m'])) {
            list($from, $to) = explode(' ', $event['m']);
            $event = array(
                'type' => 'move',
                'from' => $from,
                'to' => $to
            );
        }

        return $event;
    }
}