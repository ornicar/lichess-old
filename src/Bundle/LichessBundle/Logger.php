<?php

namespace Bundle\LichessBundle;

use Symfony\Component\Routing\Router;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Lichess\OpeningBundle\Document\Hook;

class Logger
{
    protected $logger;
    protected $generator;

    public function __construct(LoggerInterface $logger, Router $router)
    {
        $this->logger = $logger;
        $this->generator = $router->getGenerator();
    }

    protected function logPlayer(Player $player, $message, $priority)
    {
        $this->logger->addRecord($priority, $this->formatPlayer($player, $message));
    }

    protected function logGame(Game $game, $message, $priority)
    {
        $this->logger->addRecord($priority, $this->formatGame($game, $message));
    }

    protected function logHook(Hook $hook, $message, $priority)
    {
        $this->logger->addRecord($priority, $this->formatHook($hook, $message));
    }

    public function formatPlayer(Player $player, $message)
    {
        return sprintf('%s %s', $message, $this->expandPlayer($player));
    }

    public function formatGame(Game $game, $message)
    {
        return sprintf('%s %s', $message, $this->expandGame($game));
    }

    public function formatHook(Hook $hook, $message)
    {
        return sprintf('%s %s', $message, $this->expandHook($hook));
    }

    public function expandPlayer(Player $player)
    {
        return sprintf('player(%s,%s,%s,%s) %s',
            $player->getColor(),
            $player->getIsAi() ? 'AI' : $player->getUsernameWithElo(),
            $player->getGame()->hasClock() ? $player->getGame()->getClock()->getRemainingTime($player->getColor()).'s.' : '-',
            $this->generator->generate('lichess_player', array('id' => $player->getFullId()), true),
            $this->expandGame($player->getGame())
        );
    }

    public function expandGame(Game $game)
    {
        return sprintf('game(%s,%s,%s,%s,%d,%s)',
            $game->getVariantName(),
            $game->getClockName(),
            $game->getIsRated() ? 'rated' : 'casual',
            $game->getIsPlayable() ? 'playable' : $game->getStatusMessage(),
            $game->getTurns(),
            $this->generator->generate('lichess_game', array('id' => $game->getId()), true)
        );
    }

    public function expandHook(Hook $hook)
    {
        return sprintf('hook(%s,%s,%s,%s)',
            $hook->getOwnerId(),
            $hook->getUsername(),
            $hook->getVariant(),
            $hook->getMode() ? 'rated' : 'casual'
        );
    }

    public function logObject($object, $message, $priority)
    {
        if($object instanceof Player) {
            return $this->logPlayer($object, $message, $priority);
        } elseif ($object instanceof Game) {
            return $this->logGame($object, $message, $priority);
        } elseif ($object instanceof Hook) {
            return $this->logHook($object, $message, $priority);
        }

        throw new \InvalidArgumentException(sprintf('%s is nor a Game nor a Player', $object));
    }

    public function emerg($object, $message)
    {
        return $this->alert($object, $message);
    }

    public function alert($object, $message)
    {
        return $this->logObject($object, $message, 550);
    }

    public function crit($object, $message)
    {
        return $this->logObject($object, $message, 500);
    }

    public function err($object, $message)
    {
        return $this->logObject($object, $message, 400);
    }

    public function warn($object, $message)
    {
        return $this->logObject($object, $message, 300);
    }

    public function notice($object, $message)
    {
        //return $this->warn($object, $message);
    }

    public function info($object, $message)
    {
        return $this->logObject($object, $message, 200);
    }

    public function debug($object, $message)
    {
        return $this->logObject($object, $message, 100);
    }
}
