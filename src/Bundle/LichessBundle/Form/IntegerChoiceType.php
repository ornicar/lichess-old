<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Bundle\LichessBundle\Form\DataTransformer\StringToIntegerTransformer;

class IntegerChoiceType extends ChoiceType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->appendClientTransformer(new StringToIntegerTransformer());
    }
}
