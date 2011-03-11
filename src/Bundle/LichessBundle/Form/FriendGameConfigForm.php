<?php

namespace Bundle\LichessBundle\Form;

class FriendGameConfigForm extends GameConfigFormWithColor
{
    public function setVariantChoices(array $choices)
    {
        $this->add(new ChoiceField('variant', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }

    public function setTimeChoices(array $choices)
    {
        $this->add(new ChoiceField('time', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }

    public function setIncrementChoices(array $choices)
    {
        $this->add(new ChoiceField('increment', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        )));
    }

    public function submit($data)
    {
        if (!in_array($data['color'], $this->possibleColors)) {
            if ($this->logger) {
                $this->logger->warn(sprintf('%s: Invalid color submitted "%s" by %s', get_class($this), $data['color'], $_SERVER['HTTP_USER_AGENT']));
            }
            $data['color'] = $this->defaultColor;
        }

        return parent::submit($data);
    }
}
