<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class FriendWithModeGameConfigFormType extends FriendGameConfigFormType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('mode', 'choice', array(
            'choices' => $this->config->getModeChoices(),
            'multiple' => false,
            'expanded' => true
        ));
    }
}
