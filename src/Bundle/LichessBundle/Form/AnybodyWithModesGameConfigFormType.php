<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class AnybodyWithModesGameConfigFormType extends AnybodyGameConfigFormType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('modes', 'choice', array(
            'choices' => $this->config->getModeChoices(),
            'multiple' => true,
            'expanded' => true
        ));
    }
}
