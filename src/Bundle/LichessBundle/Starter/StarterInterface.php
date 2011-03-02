<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Config\GameConfig;

interface StarterInterface
{
    function start(GameConfig $config);
}
