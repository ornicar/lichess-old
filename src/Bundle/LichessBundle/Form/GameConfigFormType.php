<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\AbstractType;
use Bundle\LichessBundle\Config\GameConfig;

class GameConfigFormType extends AbstractType
{
    protected $config;

    public function __construct(GameConfig $config)
    {
        $this->config = $config;
    }
}
