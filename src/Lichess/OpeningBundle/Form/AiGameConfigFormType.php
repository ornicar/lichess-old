<?php

namespace Lichess\OpeningBundle\Form;

use Symfony\Component\Form\FormBuilder;

class AiGameConfigFormType extends GameConfigFormType
{
    public function __construct()
    {
        parent::__construct(false, false, false);
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('level', 'choice', array(
            'choices' => array_combine(range(1, 8), range(1, 8)),
            'multiple' => false,
            'expanded' => true
        ));
    }
}
