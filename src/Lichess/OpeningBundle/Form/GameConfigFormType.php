<?php

namespace Lichess\OpeningBundle\Form;

use Symfony\Component\Form\AbstractType;
use Lichess\OpeningBundle\Config\GameConfig;

class GameConfigFormType extends AbstractType
{
    protected $config;

    public function __construct(GameConfig $config)
    {
        $this->config = $config;
    }
}
