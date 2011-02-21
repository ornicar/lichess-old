<?php

namespace Bundle\LichessBundle\Form;

class FriendGameConfigForm extends GameForm
{
    public function setVariantChoices(array $choices)
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }

    public function setTimeChoices(array $choices)
    {
        $this->add(new ChoiceField('time', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }

    public function setIncrementChoices(array $choices)
    {
        $this->add(new ChoiceField('increment', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }
}
