<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\ChoiceField;

class AnybodyGameConfigForm extends GameForm
{
    public function setVariantChoices(array $choices)
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        )));
    }

    public function setTimeChoices(array $times)
    {
        $this->add(new ChoiceField('time', array(
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        )));
    }

    public function setIncrementChoices(array $increments)
    {
        $this->add(new ChoiceField('increment', array(
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
