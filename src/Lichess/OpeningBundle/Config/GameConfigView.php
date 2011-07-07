<?php

namespace Lichess\OpeningBundle\Config;

use Bundle\LichessBundle\Document\Game;

class GameConfigView
{
    /**
     * The config
     *
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getClock()
    {
        return (bool) $this->config['clock'];
    }

    public function getTime()
    {
        return $this->getClock() ? $this->config['time'] : 'Unlimited';
    }

    public function getIncrement()
    {
        return $this->config['increment'];
    }

    public function getVariant()
    {
        $variantNames = Game::getVariantNames();

        return ucfirst($variantNames[$this->config['variant']]);
    }

    public function getMode()
    {
        if ($this->config['mode'] == 1) {
            return 'Rated';
        }

        return 'Casual';
    }

    public function getColor()
    {
        return ucfirst($this->config['color']);
    }
}
