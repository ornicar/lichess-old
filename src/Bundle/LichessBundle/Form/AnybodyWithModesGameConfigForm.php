<?php

namespace Bundle\LichessBundle\Form;

class AnybodyWithModesGameConfigForm extends AnybodyGameConfigForm implements GameConfigFormWithModeInterface
{
    public function addModeChoices(array $choices)
    {
        $this->add(new ChoiceField('modes', array(
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        )));
    }

    protected function doBind(array $taintedData)
    {
        if(empty($taintedData['modes'])) {
            $taintedData['modes'] = $this->getData()->getModeChoices();
        }

        return parent::doBind($taintedData);
    }
}
