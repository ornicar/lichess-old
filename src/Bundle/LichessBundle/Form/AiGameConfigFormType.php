<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class AiGameConfigFormType extends GameConfigFormType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('color', 'text');
        $builder->add('level', 'choice', array(
            'choices' => $this->config->getLevelChoices(),
            'multiple' => false,
            'expanded' => true
        ));
        $builder->add('variant', 'choice', array(
            'choices' => $this->config->getVariantChoices(),
            'multiple' => false,
            'expanded' => true
        ));
    }
}
