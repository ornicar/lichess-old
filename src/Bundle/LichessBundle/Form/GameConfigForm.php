<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Bundle\ZendBundle\Logger\Logger;

abstract class GameConfigForm extends Form
{
    protected $logger;

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    abstract function setVariantChoices(array $times);

    public function setTimeChoices(array $times)
    {
    }

    public function setIncrementChoices(array $times)
    {
    }
}
