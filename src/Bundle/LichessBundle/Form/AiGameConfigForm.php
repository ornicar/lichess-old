<?php

namespace Bundle\LichessBundle\Form;

class AiGameConfigForm extends GameConfigFormWithColor
{
    public function setData($data)
    {
        $this->add(new ChoiceField('level', array(
            'choices' => $data->getLevelChoices(),
            'multiple' => false,
            'expanded' => true
        )));

        parent::setData($data);
    }

    public function setVariantChoices(array $choices)
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }
}
