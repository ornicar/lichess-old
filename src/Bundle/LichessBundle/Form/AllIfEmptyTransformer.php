<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\LichessBundle\Form;

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
