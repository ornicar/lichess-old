<?php

namespace Bundle\LichessBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms between a string and an integer
 */
class StringToIntegerTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        var_dump('transforming', $value);die;
        return null === $value ? null : (string) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        return null === $value ? null : (int) $value;
    }
}
