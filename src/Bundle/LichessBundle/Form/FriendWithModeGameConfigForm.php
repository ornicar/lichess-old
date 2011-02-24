<?php

namespace Bundle\LichessBundle\Form;

class FriendWithModeGameConfigForm extends FriendGameConfigForm implements GameConfigFormWithModeInterface
{
    public function addModeChoices(array $choices)
    {
        $this->add(new ChoiceField('mode', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }
}
