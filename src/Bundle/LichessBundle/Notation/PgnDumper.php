<?php

namespace Bundle\LichessBundle\Notation;
use Bundle\LichessBundle\Entities\Game;

class PgnDumper
{
    /**
     * Dumper options
     *
     * @var array
     */
    protected $options = array();
    
    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Dumps a single move to PGN notation
     *
     * @return string
     **/
    public function dumpMove(Game $game, $move)
    {
        
    }

    /**
     * Produces PGN notation for a game
     *
     * @return string
     **/
    public function dumpGame(Game $game)
    {
        $result = $this->getPgnResult($game);
        $header = $this->getPgnHeader($game);
        $moves = $this->getPgnMoves($game);

        $pgn = $header."\n".$moves;

        if(!empty($moves)) {
            $pgn .= ' ';
        }

        $pgn .= $result;

        return $pgn;
    }
        return sprintf('[Site "%s"]
[Date "%s"]
[Result "%s"]

%s %s
', 'http://lichess.org/', date('Y.m.d', $game->getUpdatedAt()), $pgnResult, $game->getPgnMoves(), $pgnResult);
    }

    protected function getPgnHeader(Game $game)
    {
        return sprintf('[Site "%s"]
[Date "%s"]
[Result "%s"]',
            'http://lichess.org/', date('Y.m.d', $game->getUpdatedAt()), $this->getPgnResult($game)
        );
    }

    protected function getPgnResult(Game $game)
    {
        if($game->getIsFinished()) {
            if($game->getPlayer('white')->getIsWinner()) {
                return '1-0';
            }
            elseif($game->getPlayer('black')->getIsWinner()) {
                return '0-1';
            }
            return '1/2-1/2';
        }
        return '*';
    }

    /**
     * Get options
     * @return array
     */
    public function getOptions()
    {
      return $this->options;
    }
    
    /**
     * Set options
     * @param  array
     * @return null
     */
    public function setOptions($options)
    {
      $this->options = $options;
    }
}
