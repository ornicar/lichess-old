<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class AnybodyGameConfigFormType extends GameConfigFormType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        foreach ($this->getConfigChoices() as $property => $choices) {
            $builder->add($property, 'choice', array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true
            ));
            $builder->appendNormTransformer(new AllIfEmptyTransformer($property, $choices));
        }
    }

    protected function getConfigChoices()
    {
        $config = $this->config;

        return array(
            'variants' => $config::getVariantChoices(),
            'times' => $config::getTimeChoices(),
            'increments' => $config::getIncrementChoices()
        );
    }
}
