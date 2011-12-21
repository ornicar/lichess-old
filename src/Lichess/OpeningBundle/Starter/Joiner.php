<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Logger;
use Symfony\Component\Routing\Router;
use InvalidArgumentException;
use Bundle\LichessBundle\Chess\GameEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Joiner
{
    protected $playerBlamer;
    protected $urlGenerator;
    protected $dispatcher;
    protected $logger;

    public function __construct(PlayerBlamer $playerBlamer, Router $router, EventDispatcherInterface $dispatcher, Logger $logger)
    {
        $this->playerBlamer = $playerBlamer;
        $this->urlGenerator = $router->getGenerator();
        $this->dispatcher   = $dispatcher;
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

        $event = new GameEvent($game);
        $this->dispatcher->dispatch('lichess_game.start', $event);
    }
}
