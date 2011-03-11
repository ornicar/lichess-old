<?php

namespace Bundle\LichessBundle\Config;
use Bundle\LichessBundle\Document\Game;

abstract class GameConfig
{
    protected $timeChoices      = array(2, 5, 10, 20, 0);
    protected $incrementChoices = array(0, 2, 5, 10, 20);
    protected $modeChoices      = array(0 => 'Casual', 1 => 'Rated');

    abstract public function toArray();

    abstract public function fromArray(array $data);

    public function getModeChoices()
    {
        return $this->modeChoices;
    }

    public function getVariantChoices()
    {
        $choices = array();
        foreach(Game::getVariantNames() as $code => $name) {
            $choices[$code] = ucfirst($name);
        }

        return $choices;
    }

    public function getTimeChoices()
    {
        $choices = array();
        foreach($this->timeChoices as $time) {
            $choices[$time] = $this->renameTime($time);
        }

        return $choices;
    }

    public function getIncrementChoices()
    {
        $choices = array();
        foreach($this->incrementChoices as $increment) {
            $choices[$increment] = $increment;
        }

        return $choices;
    }

    protected function renameTime($time)
    {
        if($time) {
            return $time;
        }

        return 'Unlimited';
    }
}
