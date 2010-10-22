<?php

namespace Bundle\LichessBundle\Form;

abstract class GameConfig
{
    protected $timeChoices = array(5, 10, 20, 30, 0);
    protected $translator;

    public function __construct($translator = null)
    {
        $this->translator = $translator;
    }

    public function getTimeChoices()
    {
        if(!$this->translator) {
            throw new \LogicException('You must pass a translator instance');
        }

        $choices = array();
        foreach($this->timeChoices as $time) {
            $choices[$time] = $this->getName($time);
        }

        return $choices;
    }

    protected function getName($time)
    {
        return $time ? $this->translator->_('%nb% minutes/side', array('%nb%' => $time)) : $this->translator->_('no clock');
    }
}
