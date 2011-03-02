<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\HiddenField;

abstract class GameConfigForm extends Form
{
    public function configure()
    {
        // hack to have functional tests passing
        $this->add(new HiddenField('color'));
    }

    abstract function setVariantChoices(array $times);

    public function setTimeChoices(array $times)
    {
    }

    public function setIncrementChoices(array $times)
    {
    }
}
