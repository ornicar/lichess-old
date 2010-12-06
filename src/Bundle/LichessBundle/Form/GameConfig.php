<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Document\Game;

abstract class GameConfig
{
    protected $timeChoices = array(2, 5, 10, 20, 0);

    protected $modeChoices = array(0 => 'Casual', 1 => 'Rated');

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

    protected function renameTime($time)
    {
        if($time) {
            return $time;
        }

        return 'Unlimited';
    }
}
