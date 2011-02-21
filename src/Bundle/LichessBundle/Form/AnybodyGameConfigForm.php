<?php

namespace Bundle\LichessBundle\Form;

class AnybodyGameConfigForm extends GameForm
{
    public function setVariantChoices(array $choices)
    {
        $this->add(new ChoiceField('variants', array(
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        )));
    }

    public function setTimeChoices(array $choices)
    {
        $this->add(new ChoiceField('times', array(
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        )));
    }

    public function setIncrementChoices(array $choices)
    {
        $this->add(new ChoiceField('increments', array(
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        )));
    }

    protected function doBind(array $taintedData)
    {
        foreach(array('variants', 'times', 'increments') as $fieldName) {
            if(empty($taintedData[$fieldName])) {
                $taintedData[$fieldName] = $this[$fieldName]->getOption('choices');
            }
        }

        return parent::doBind($taintedData);
    }
}
