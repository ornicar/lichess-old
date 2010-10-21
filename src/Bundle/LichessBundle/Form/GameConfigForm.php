<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;

class GameConfigForm extends Form
{
    public function configure()
    {
        foreach(GameConfig::$timeChoices as $timeChoice) {
            $this->add(new CheckboxField($timeChoice, array('label' => $this->getLabel($timeChoice))));
        }
    }

    protected function getLabel($time)
    {
        return $time ? '%nb% minutes/side' : 'no clock';
    }
}
