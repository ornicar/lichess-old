<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Logger;
use Symfony\Component\Routing\Router;
use InvalidArgumentException;

class Joiner
{
    protected $starter;
    protected $playerBlamer;

    public function __construct(GameStarter $starter, PlayerBlamer $playerBlamer)
    {
        $this->starter = $starter;
        $this->playerBlamer = $playerBlamer;
    }

    public function join(Player $player)
    {
        $game = $player->getGame();

        if($game->getIsStarted()) {
            throw new InvalidArgumentException('Cannot join started game');
        }

        $this->playerBlamer->blame($player);

        return $this->starter->start($game);
    }
}
