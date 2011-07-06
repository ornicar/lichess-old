<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Logger;
use Symfony\Component\Routing\Router;
use InvalidArgumentException;

class Joiner
{
    protected $playerBlamer;
    protected $urlGenerator;
    protected $logger;

    public function __construct(PlayerBlamer $playerBlamer, Router $router, Logger $logger)
    {
        $this->playerBlamer = $playerBlamer;
        $this->urlGenerator = $router->getGenerator();
        $this->logger       = $logger;
    }

    public function join(Player $player)
    {
        $game = $player->getGame();

        if($game->getIsStarted()) {
            $this->logger->warn($player, 'Game:join started');
            throw new InvalidArgumentException('Cannot join started game');
        }

        $this->playerBlamer->blame($player);
        $game->start();
        $player->getOpponent()->addEventToStack(array(
            'type' => 'redirect',
            'url'  => $this->urlGenerator->generate('lichess_player', array('id' => $player->getOpponent()->getFullId()))
        ));
        $this->logger->notice($player, 'Game:join');
    }
}
