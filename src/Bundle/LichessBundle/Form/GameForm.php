<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;

abstract class GameForm extends Form
{
    public function setColorChoices(array $choices)
    {
        $this->add(new ChoiceField('color', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }

    abstract function setVariantChoices(array $times);

    public function setTimeChoices(array $times)
    {
    }

    public function setIncrementChoices(array $times)
    {
    }
}
