<?php

namespace Lichess\OpeningBundle\Form;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\DataTransformerInterface;

class AllIfEmptyTransformer implements DataTransformerInterface
{
    private $property;
    private $choices;

    public function __construct($property, array $choices)
    {
        $this->property = $property;
        $this->choices = $choices;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        if (0 === count($value->{'get'.$this->property}())) {
            $value->{'set'.$this->property}(array_keys($this->choices));
        }

        return $value;
    }
}
