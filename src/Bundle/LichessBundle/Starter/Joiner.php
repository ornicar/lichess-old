<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
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

    public function join(Game $game)
    {
        if($game->getIsStarted()) {
            $this->logger->warn($game, 'Game:join started');
            throw new InvalidArgumentException('Cannot join started game');
        }

        $this->playerBlamer->blame($game->getInvited());
        $game->start();
        $game->getCreator()->addEventToStack(array(
            'type' => 'redirect',
            'url'  => $this->urlGenerator->generate('lichess_player', array('id' => $game->getCreator()->getFullId()))
        ));
        $this->logger->notice($game, 'Game:join');
    }
}
