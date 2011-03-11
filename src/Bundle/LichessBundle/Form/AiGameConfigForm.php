<?php

namespace Bundle\LichessBundle\Form;

class AiGameConfigForm extends GameConfigFormWithColor
{
    public function setVariantChoices(array $choices)
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }
}
