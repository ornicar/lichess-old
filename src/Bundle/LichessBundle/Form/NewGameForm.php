<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;

class NewGameForm extends Form
{
    public function configure()
    {
        foreach(NewGame::$timeChoices as $timeChoice) {
            $this->add(new CheckboxField($timeChoice, array('label' => $this->getLabel($timeChoice))));
        }
    }

    protected function getLabel($time)
    {
        return $time ? '%nb% minutes/side' : 'no clock';
    }
}
