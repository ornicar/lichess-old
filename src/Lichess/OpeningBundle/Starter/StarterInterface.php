<?php

namespace Lichess\OpeningBundle\Starter;

use Lichess\OpeningBundle\Config\GameConfig;

interface StarterInterface
{
    function start(GameConfig $config);
}
