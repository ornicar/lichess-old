<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Model\Game;

abstract class GameConfig
{
    protected $timeChoices = array(2, 5, 10, 20, 0);

    abstract public function toArray();

    abstract public function fromArray(array $data);

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
            $choices[$time] = $this->getTimeName($time);
        }

        return $choices;
    }

    protected function getTimeName($time)
    {
        if($time) {
            return $time;
        }

        return 'Unlimited';
    }
}
