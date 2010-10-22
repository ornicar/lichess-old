<?php

namespace Bundle\LichessBundle\Persistence;

class QueueEntry
{
    public $id;
    public $times;
    public $gameHash;

    public function __construct(array $times)
    {
        $this->times = $times;
    }

    public function match(QueueEntry $entry)
    {
        $matches = array_intersect($entry->times, $this->times);

        return !empty($matches);
    }
}
