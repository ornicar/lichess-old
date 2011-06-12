<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class FriendGameConfigFormType extends GameConfigFormType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('color', 'text');
        $builder->add('variant', 'choice', array(
            'choices' => $this->config->getVariantChoices(),
            'multiple' => false,
            'expanded' => true
        ));
        $builder->add('time', 'choice', array(
            'choices' => $this->config->getTimeChoices(),
            'multiple' => false,
            'expanded' => true
        ));
        $builder->add('increment', 'choice', array(
            'choices' => $this->config->getIncrementChoices(),
            'multiple' => false,
            'expanded' => true
        ));
    }
}
