<?php

namespace Bundle\LichessBundle\Persistence;

class QueueEntry
{
    public $id;
    public $times;
    public $gameHash;
    public $userId;

    public function __construct(array $times, $userId)
    {
        $this->times = $times;
        $this->userId = $userId;
    }

    public function match(QueueEntry $entry)
    {
        if($entry->userId == $this->userId) {
            return false;
        }

        $matches = array_intersect($entry->times, $this->times);

        return !empty($matches);
    }

    public function getCommonTime(QueueEntry $entry)
    {
        $matches = array_values(array_intersect($entry->times, $this->times));

        if(count($matches) < 3) {
            return $matches[0];
        }

        return $matches[1];
    }
}
