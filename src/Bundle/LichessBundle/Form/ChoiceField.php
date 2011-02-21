<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\ChoiceField as BaseChoiceField;
use Symfony\Component\Form\Field;

class ChoiceField extends BaseChoiceField
{
    /**
     * Use == instead of === because of '1' beeing string or iny
     */
    protected function transform($value)
    {
        if ($this->isExpanded()) {
            $value = Field::transform($value);
            $choices = $this->getChoices();

            foreach ($choices as $choice => $_) {
                $choices[$choice] = $this->isMultipleChoice()
                    ? in_array($choice, (array)$value)
                    : ($choice == $value);
            }

            return $choices;
        }

        return parent::transform($value);
    }
}
