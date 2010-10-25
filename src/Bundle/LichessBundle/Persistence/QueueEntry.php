<?php

namespace Bundle\LichessBundle\Persistence;

class QueueEntry
{
    public $id;
    public $times;
    public $variants;
    public $gameHash;
    public $userId;

    public function __construct(array $times, array $variants, $userId)
    {
        $this->times = $times;
        $this->variants = $variants;
        $this->userId = $userId;
    }

    public function match(QueueEntry $entry)
    {
        if($entry->userId == $this->userId) {
            return false;
        }

        if(0 === count(array_intersect($entry->variants, $this->variants))) {
            return false;
        }

        if(0 === count(array_intersect($entry->times, $this->times))) {
            return false;
        }

        return true;
    }

    public function getCommonTime(QueueEntry $entry)
    {
        $matches = array_values(array_intersect($entry->times, $this->times));

        if(count($matches) < 3) {
            return $matches[0];
        }

        return $matches[1];
    }

    public function getCommonVariant(QueueEntry $entry)
    {
        $matches = array_values(array_intersect($entry->variants, $this->variants));

        if(1 === count($matches)) {
            return $matches[0];
        }

        return $matches[mt_rand(0, 1)];
    }
}
