<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class AnybodyGameConfigFormType extends GameConfigFormType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('variants', 'choice', array(
            'choices' => $this->config->getVariantChoices(),
            'multiple' => true,
            'expanded' => true
        ));
        $builder->add('times', 'choice', array(
            'choices' => $this->config->getTimeChoices(),
            'multiple' => true,
            'expanded' => true
        ));
        $builder->add('increments', 'choice', array(
            'choices' => $this->config->getIncrementChoices(),
            'multiple' => true,
            'expanded' => true
        ));
    }
}
