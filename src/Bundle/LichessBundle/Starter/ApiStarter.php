<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Config\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Document\Clock;
use Bundle\LichessBundle\Sync\Memory;

use Doctrine\ODM\MongoDB\DocumentManager;

class ApiStarter implements StarterInterface
{
    protected $generator;
    protected $playerBlamer;
    protected $objectManager;
    protected $memory;
    protected $logger;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, DocumentManager $objectManager, Memory $memory, Logger $logger)
    {
        $this->generator     = $generator;
        $this->playerBlamer  = $playerBlamer;
        $this->objectManager = $objectManager;
        $this->memory        = $memory;
        $this->logger        = $logger;
    }

    public function start(GameConfig $config)
    {
        $color = $config->resolveColor();
        $player = $this->generator->createGameForPlayer($color, $config->variant);
        $game = $player->getGame();
        if($config->time) {
            $clock = new Clock($config->time * 60, $config->increment);
            $game->setClock($clock);
        }
        $game->setIsRated($config->mode);
        $this->objectManager->persist($game);
        $this->logger->notice($game, 'Game:api create');

        return $player;
    }
}
