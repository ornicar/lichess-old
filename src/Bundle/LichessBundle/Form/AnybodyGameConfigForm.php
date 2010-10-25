<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Validator\Validator;

class AnybodyGameConfigForm extends Form
{
    public function configure()
    {
        $this->add(new ChoiceField('variants', array(
            'choices' => $this->getData()->getVariantChoices(),
            'multiple' => true,
            'expanded' => true
        )));
        $this->add(new ChoiceField('times', array(
            'choices' => $this->getData()->getTimeChoices(),
            'multiple' => true,
            'expanded' => true
        )));
    }
}
