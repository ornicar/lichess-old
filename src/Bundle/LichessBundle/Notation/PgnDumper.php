<?php

namespace Bundle\LichessBundle\Notation;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Square;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * http://www.chessclub.com/help/PGN-spec
 */
class PgnDumper
{
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator = null)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Produces PGN notation for a game
     *
     * @return string
     **/
    public function dumpGame(Game $game, $withTime = false)
    {
        $result = $this->getPgnResult($game);
        $header = $this->getPgnHeader($game);
        $moves = $this->getPgnMoves($game, $withTime);

        $pgn = $header."\n\n".$moves;

        if(!empty($moves)) {
            $pgn .= ' ';
        }

        $pgn .= $result;

        return $pgn;
    }

    public function getPgnMoves(Game $game, $withTime = false)
    {
        $withTime = $withTime && $game->hasMoveTimes();
        if ($withTime) {
            $times = $game->getMoveTimes();
        }
        $pgnMoves = $game->getPgnMoves();
        if(empty($pgnMoves)) {
            return '';
        }
        $moves = $game->getPgnMoves();
        $nbMoves = count($moves);
        $nbTurns = ceil($nbMoves/2);
        $string = '';
        for($turns = 1; $turns <= $nbTurns; $turns++) {
            $index = ($turns-1)*2;
            $string .= $turns.'. ';
            $string .= $moves[$index].' ';
            if ($withTime) {
                $string .= '{'.$times[$index].'} ';
            }
            if(isset($moves[$index+1])) {
                $string .= $moves[$index+1].' ';
                if ($withTime) {
                    $string .= '{'.$times[$index+1].'} ';
                }
            }
        }

        return trim($string);
    }

    protected function getPgnHeader(Game $game)
    {
        $header = sprintf('[Event "%s"]%s[Site "%s"]%s[Date "%s"]%s[White "%s"]%s[Black "%s"]%s[WhiteElo "%s"]%s[BlackElo "%s"]%s[Result "%s"]%s[PlyCount "%d"]%s[Variant "%s"]',
            $this->getEventName($game), "\n",
            $this->getGameUrl($game), "\n",
            $this->getGameDate($game), "\n",
            $this->getPgnPlayer($game->getPlayer('white')), "\n",
            $this->getPgnPlayer($game->getPlayer('black')), "\n",
            $this->getPgnPlayerElo($game->getPlayer('white')), "\n",
            $this->getPgnPlayerElo($game->getPlayer('black')), "\n",
            $this->getPgnResult($game), "\n",
            $game->getTurns(), "\n",
            ucfirst($game->getVariantName())
        );

        if (!$game->isStandardVariant()) {
            $header .= sprintf('%s[FEN "%s"]', "\n", $game->getInitialFen());
        }

        return $header;
    }

    protected function getEventName($game)
    {
        return $game->getIsRated() ? 'Rated Game' : 'Casual game';
    }

    protected function getGameDate($game)
    {
        return $game->getCreatedAt() ? $game->getCreatedAt()->format('Y.m.d') : '?';
    }

    protected function getGameUrl(Game $game)
    {
        if(null === $this->urlGenerator) {
            return 'http://lichess.org/';
        }

        return $this->urlGenerator->generate('lichess_pgn_viewer', array('id' => $game->getId()), true);
    }

    protected function getPgnResult(Game $game)
    {
        if($game->getIsFinished()) {
            if($game->getPlayer('white')->getIsWinner()) {
                return '1-0';
            } elseif($game->getPlayer('black')->getIsWinner()) {
                return '0-1';
            }
            return '1/2-1/2';
        }
        return '*';
    }

    protected function getPgnPlayer(Player $player)
    {
        if($player->getIsAi()) {
            return 'Crafty level '.$player->getAiLevel();
        }

        return $player->getUsername();
    }

    protected function getPgnPlayerElo(Player $player)
    {
        if($player->getIsAi()) {
            return '?';
        }
        if (!$player->getElo()) {
            return '?';
        }

        return $player->getElo();
    }
}
