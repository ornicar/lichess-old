<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Validator\Validator;

class AiGameConfigForm extends Form
{
    public function configure()
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $this->getData()->getVariantChoices(),
            'multiple' => false,
            'expanded' => true
        )));
    }
}
