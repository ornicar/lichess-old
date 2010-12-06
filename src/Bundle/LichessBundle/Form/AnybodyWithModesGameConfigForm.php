<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\ChoiceField;

class AnybodyWithModesGameConfigForm extends AnybodyGameConfigForm
{
    public function configure()
    {
        parent::configure();

        $this->add(new ChoiceField('modes', array(
            'choices' => $this->getData()->getModeChoices(),
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
