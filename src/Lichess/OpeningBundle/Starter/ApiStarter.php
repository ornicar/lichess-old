<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Document\Clock;
use Bundle\LichessBundle\Sync\Memory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Lichess\OpeningBundle\Config\GameConfig;

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
        $player = $this->generator->createGameForPlayer($color, $config->getVariant());
        $game = $player->getGame();
        if($config->getClock()) {
            $clock = new Clock($config->getTime() * 60, $config->getIncrement());
            $game->setClock($clock);
        }
        $game->setIsRated($config->getMode());
        $game->compress();
        $this->objectManager->persist($game);
        $this->logger->notice($game, 'Game:api create');

        return $player;
    }
}
