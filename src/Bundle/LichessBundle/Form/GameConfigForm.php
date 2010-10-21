<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;

class GameConfigForm extends Form
{
    public function configure()
    {
        $this->add(new ChoiceField('times', array(
            'choices' => $this->getData()->getTimeChoices(),
            'multiple' => true,
            'expanded' => true
        )));
    }
}
