<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\ChoiceField;

class FriendWithModeGameConfigForm extends FriendGameConfigForm
{
    public function configure()
    {
        parent::configure();

        $this->add(new ChoiceField('mode', array(
            'choices' => $this->getData()->getModeChoices(),
            'multiple' => false,
            'expanded' => true
        )));
    }
}
