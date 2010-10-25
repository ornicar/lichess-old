<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Validator\Validator;

class FriendGameConfigForm extends Form
{
    public function configure()
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $this->getData()->getVariantChoices(),
            'multiple' => false,
            'expanded' => true
        )));
        $this->add(new ChoiceField('time', array(
            'choices' => $this->getData()->getTimeChoices(),
            'multiple' => false,
            'expanded' => true
        )));
    }
}
