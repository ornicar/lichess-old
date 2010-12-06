<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;

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

    protected function doBind(array $taintedData)
    {
        if(empty($taintedData['variants'])) {
            $taintedData['variants'] = $this->getData()->getVariantChoices();
        }
        if(empty($taintedData['times'])) {
            $taintedData['times'] = $this->getData()->getTimeChoices();
        }

        return parent::doBind($taintedData);
    }
}
