<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;

abstract class GameForm extends Form
{
    abstract function setVariantChoices(array $times);

    public function setTimeChoices(array $times)
    {
    }

    public function setIncrementChoices(array $times)
    {
    }
}
