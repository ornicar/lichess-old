<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Form\GameConfig;

interface StarterInterface
{
    function start(GameConfig $config, $color);
}
