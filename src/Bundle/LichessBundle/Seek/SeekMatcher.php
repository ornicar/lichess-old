<?php

namespace Bundle\LichessBundle\Seek;

use Bundle\LichessBundle\Document\Seek;

class SeekMatcher
{
    protected $useSession;

    public function __construct($useSession = true)
    {
        $this->useSession = (bool) $useSession;
    }

    public function match(Seek $a, Seek $b)
    {
        if($this->useSession && $a->getSessionId() === $b->getSessionId()) {
            return false;
        }

        return true
            && false !== $this->getCommonVariant($a, $b)
            && false !== $this->getCommonTime($a, $b)
            && false !== $this->getCommonMode($a, $b);
    }

    public function getCommonVariant(Seek $a, Seek $b)
    {
        $matches = array_values(array_intersect($a->getVariants(), $b->getVariants()));

        if(empty($matches)) {
            return false;
        }

        if(1 === count($matches)) {
            return $matches[0];
        }

        // choose 960 when possible
        return $matches[1];
    }

    public function getCommonTime(Seek $a, Seek $b)
    {
        $matches = array_values(array_intersect($a->getTimes(), $b->getTimes()));

        if(empty($matches)) {
            return false;
        }

        if(count($matches) < 3) {
            return $matches[0];
        }

        return $matches[1];
    }

    public function getCommonIncrement(Seek $a, Seek $b)
    {
        $matches = array_values(array_intersect($a->getIncrements(), $b->getIncrements()));

        if(empty($matches)) {
            return $matches = array_values(array_merge($a->getIncrements(), $b->getIncrements()));
        }

        if(1 === count($matches)) {
            return $matches[0];
        }

        return $matches[mt_rand(0, count($matches)-1)];
    }

    public function getCommonMode(Seek $a, Seek $b)
    {
        $matches = array_values(array_intersect($a->getModes(), $b->getModes()));

        if(empty($matches)) {
            return false;
        }

        if(1 === count($matches)) {
            return $matches[0];
        }

        // rated mode when possible
        return $matches[1];
    }
}
