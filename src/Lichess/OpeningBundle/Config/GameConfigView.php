<?php

namespace Lichess\OpeningBundle\Config;

use Bundle\LichessBundle\Document\Game;

class GameConfigView
{
    /**
     * The config
     *
     * @var GameConfig
     */
    protected $config;

    public function __construct(GameConfig $config)
    {
        $this->config = $config;
    }

    public function getClock()
    {
        return $this->config->getClock();
    }

    public function getTime()
    {
        return $this->config->getClock() ? $this->config->getTime() : 'Unlimited';
    }

    public function getIncrement()
    {
        return $this->config->getIncrement();
    }

    public function getVariant()
    {
        $variantNames = Game::getVariantNames();

        return ucfirst($variantNames[$this->config->getVariant()]);
    }

    public function getMode()
    {
        if ($this->config->getMode() == 1) {
            return 'Rated';
        }

        return 'Casual';
    }
}
