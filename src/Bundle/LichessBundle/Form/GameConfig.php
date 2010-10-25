<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Entities\Game;

abstract class GameConfig
{
    protected $timeChoices = array(5, 10, 20, 0);
    protected $translator;

    public function __construct($translator = null)
    {
        $this->translator = $translator;
    }

    abstract public function toArray();

    abstract public function fromArray(array $data);

    public function getVariantChoices()
    {
        $choices = array();
        foreach(Game::getVariantNames() as $code => $name) {
            $choices[$code] = $name;
        }

        return $choices;
    }

    public function getTimeChoices()
    {
        if(!$this->translator) {
            throw new \LogicException('You must pass a translator instance');
        }

        $choices = array();
        foreach($this->timeChoices as $time) {
            $choices[$time] = $this->getTimeName($time);
        }

        return $choices;
    }

    protected function getTimeName($time)
    {
        return $time ? $this->translator->_('%nb% minutes/side', array('%nb%' => $time)) : $this->translator->_('no clock');
    }
}
