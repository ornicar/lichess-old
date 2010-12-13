<?php

namespace Bundle\LichessBundle;

use Symfony\Bundle\ZendBundle\Logger\Logger as BaseLogger;
use Symfony\Component\Routing\Router;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;

class Logger
{
    protected $logger;
    protected $generator;

    public function __construct(BaseLogger $logger, Router $router)
    {
        $this->logger = $logger;
        $this->generator = $router->getGenerator();
    }

    protected function logPlayer(Player $player, $message, $priority)
    {
        $this->logger->log($this->formatPlayer($player, $message), $priority);
    }

    protected function logGame(Game $game, $message, $priority)
    {
        $this->logger->log($this->formatGame($game, $message), $priority);
    }

    public function formatPlayer(Player $player, $message)
    {
        return sprintf('%s %s', $message, $this->expandPlayer($player));
    }

    public function formatGame(Game $game, $message)
    {
        return sprintf('%s %s', $message, $this->expandGame($game));
    }

    public function expandPlayer(Player $player)
    {
        return sprintf('player(%s,%s,%s,%s) %s',
            $player->getColor(),
            $player->getIsAi() ? 'AI' : $player->getUsernameWithElo(),
            $player->getGame()->hasClock() ? $player->getGame()->getClock()->getRemainingTime($player->getColor()) : '-',
            $this->generator->generate('lichess_player', array('id' => $player->getFullId()), true),
            $this->expandGame($player->getGame())
        );
    }

    public function expandGame(Game $game)
    {
        return sprintf('game(%s,%s,%s,%s,%s,%d)',
            $game->getVariantName(),
            $game->getClockName(),
            $game->getIsRated() ? 'rated' : 'casual',
            $game->getIsPlayable() ? 'playable' : $game->getStatusMessage(),
            $game->getTurns(),
            $this->generator->generate('lichess_game', array('id' => $game->getId()), true)
        );
    }

    public function logObject($object, $message, $priority)
    {
        if($object instanceof Player) {
            return $this->logPlayer($object, $message, $priority);
        } elseif ($object instanceof Game) {
            return $this->logGame($object, $message, $priority);
        }

        throw new \InvalidArgumentException(sprintf('%s is nor a Game nor a Player', $object));
    }

    public function emerg($object, $message)
    {
        return $this->logObject($object, $message, 0);
    }

    public function alert($object, $message)
    {
        return $this->logObject($object, $message, 1);
    }

    public function crit($object, $message)
    {
        return $this->logObject($object, $message, 2);
    }

    public function err($object, $message)
    {
        return $this->logObject($object, $message, 3);
    }

    public function warn($object, $message)
    {
        return $this->logObject($object, $message, 4);
    }

    public function notice($object, $message)
    {
        return $this->logObject($object, $message, 5);
    }

    public function info($object, $message)
    {
        return $this->logObject($object, $message, 6);
    }

    public function debug($object, $message)
    {
        return $this->logObject($object, $message, 7);
    }
}
