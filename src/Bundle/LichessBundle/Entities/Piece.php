<?php

namespace Bundle\LichessBundle\Entities;

abstract class Piece
{
    /**
     * the player that owns the piece
     *
     * @var Player
     */
    protected $player = null;

    /**
     * X position
     *
     * @var int
     */
    protected $x = null;

    /**
     * Y position
     *
     * @var int
     */
    protected $y = null;

    /**
     * Whether the piece is dead or not
     *
     * @var boolean
     */
    protected $isDead = false;

    /**
     * When this piece moved for the first time (usefull for en passant)
     *
     * @var int
     */
    protected $firstMove = 0;

    /**
     * Unique hash
     *
     * @var string
     */
    protected $hash = null;

    /**
     * Non-persistent processing cache 
     * 
     * @var array
     */
    protected $cache = array();
    
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->hash = substr(\sha1(\uniqid().\mt_rand().microtime(true)), 0, 6);
    }

    /**
     * @return string
     */
    public function getHash()
    {
      return $this->hash;
    }

    /**
     * @return array
     */
    abstract protected function getBasicTargetSquares();

    /**
     * @return string
     */
    abstract public function getClass();

    /**
     * @return integer
     */
    public function getFirstMove()
    {
        return $this->firstMove;
    }

    /**
     * @param integer
     */
    public function setFirstMove($firstMove)
    {
        $this->firstMove = $firstMove;
    }

    /**
     * @return boolean
     */
    public function getIsDead()
    {
        return $this->isDead;
    }

    /**
     * @param boolean
     */
    public function setIsDead($isDead)
    {
        $this->isDead = $isDead;
    }

    /**
     * @return boolean
     */
    public function isClass($class)
    {
        return $this->getClass() === $class;
    }

    /**
     * @return integer
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param integer
     */
    public function setY($y)
    {
        $this->y = $y;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param int
     */
    public function setX($x)
    {
        $this->x = $x;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param Player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    public function getTargetKeys($protectKing = true)
    {
        return $this->getBoard()->squaresToKeys($this->getTargetSquares($protectKing));
    }

    public function getTargetSquares($protectKing = true)
    {
        $targets = $this->getBoard()->cleanSquares($this->getBasicTargetSquares());

        if ($protectKing)
        {
            $targets = $this->protectKingFilter($targets);
        }

        return $targets;
    }

    protected function getTargetsByProjection($x, $y)
    {
        $squares = array();
        $continue = true;

        $square = $this->getSquare();

        while($continue)
        {
            if ($square = $square->getSquareByRelativePos($x, $y))
            {
                if ($otherPiece = $square->getPiece())
                {
                    if (!$otherPiece->getPlayer()->is($this->getPlayer()))
                    {
                        $squares[] = $square;
                    }

                    $continue = false;
                }
                else
                {
                    $squares[] = $square;
                }
            }
            else
            {
                $continue = false;
            }
        }

        return $squares;
    }

    // prevent a piece to eat a friend
    protected function cannibalismFilter(array $targets)
    {
        foreach($targets as $it => $target)
        {
            if ($target && ($piece = $target->getPiece()) && ($piece->getPlayer()->is($this->getPlayer())))
            {
                unset($targets[$it]);
            }
        }

        return $targets;
    }

    // prevent leaving the king without protection
    protected function protectKingFilter(array $targets)
    {
        if(empty($targets))
        {
            return $targets;
        }

        $king = $this->getPlayer()->getKing();
        $kingSquareKey = $king->getSquareKey();

        // create virtual objects
        $_game        = $this->getGame()->getCopy();
        $_game->getBoard()->clearCache();
        $_thisSquare  = $_game->getBoard()->getSquareByKey($this->getSquareKey());
        $_this        = $_thisSquare->getPiece();
        $_player      = $_this->getPlayer();
        $_opponent    = $_player->getOpponent();

        // if we are moving the king, or if king is attacked, verify every opponent pieces
        if ($_this->isType('king') || $king->isAttacked())
        {
            $_opponentPieces = $this->getPieceFilter()->filterAlive($_opponent->get('Pieces'));
        }
        // otherwise only verify projection pieces: bishop, rooks and queens
        else
        {
            $_opponentPieces = $this->getPieceFilter()->filterAlive($this->getPieceFilter()->filterProjection($_opponent->get('Pieces')));
        }

        foreach($targets as $it => $square)
        {
            $_square = $_game->getBoard()->getSquareByKey($square->getKey());

            // kings move to its target
            if ($_this->isType('king'))
            {
                $kingSquareKey = $square->getKey();
            }

            // killed opponent piece
            if ($_killedPiece = $_square->getPiece())
            {
                $_killedPiece->kill(false);
            }

            $_this->set('x', $_square->getX());
            $_this->set('y', $_square->getY());

            $_game->getBoard()->compile();

            foreach($_opponentPieces as $_opponentPiece)
            {
                if ($_opponentPiece->get('is_dead'))
                {
                    continue;
                }

                // if our king gets attacked
                if (in_array($kingSquareKey, $_opponentPiece->getTargetKeys(false)))
                {
                    // can't go here
                    unset($targets[$it]);
                    break;
                }
            }

            // if a virtual piece has been killed, bring it back to life
            if ($_killedPiece)
            {
                $_killedPiece->set('is_dead', 0);
                $_killedPiece->set('x', $_square->getX());
                $_killedPiece->set('y', $_square->getY());
            }
        }

        // restore position
        $_this->set('x', $this->getX());
        $_this->set('y', $this->getY());

        return $targets;
    }

    public function moveToPos($x, $y, $checkMoveIntegrity = true, array $options = array())
    {
        return $this->moveToSquare($this->Board->getSquarebyPos($x, $y), $checkMoveIntegrity, $options);
    }

    public function moveToSquareKey($squareKey, $checkMoveIntegrity = true, array $options = array())
    {
        return $this->moveToSquare($this->Board->getSquarebyKey($squareKey), $checkMoveIntegrity, $options);
    }

    public function moveToSquare(Square $square, $checkMoveIntegrity = true, array $options = array())
    {
        return $this->getPlayer()->movePieceToSquare($this, $square, $checkMoveIntegrity, $options);
    }

    public function kill($andSave = true)
    {
        $this->set('is_dead', $this->getGame()->get('turns'));
        $this->set('x', null);
        $this->set('y', null);

        if ($andSave)
        {
            $this->save();
        }
    }

    public function canMoveToSquare(Square $square)
    {
        return in_array($square->getKey(), $this->getTargetKeys());
    }

    public function getSquare()
    {
        return $this->getBoard()->getSquareByKey($this->getSquareKey());
    }

    public function getGame()
    {
        return $this->player->getGame();
    }

    public function getBoard()
    {
        return $this->getGame()->getBoard();
    }

    public function getPieceFilter()
    {
        return $this->getPlayer()->getTable()->getPieceFilter();
    }

    public function getSquareKey()
    {
        return 's'.$this->getX().$this->getY();
    }

    public function toDebug()
    {
        $pos = ($square = $this->getSquare()) ? $square->getHumanPos() : 'no-pos';

        return $this->id.': '.$this->type.' '.$this->color.' in '.$pos;
    }

    public function __toString()
    {
        return $this->toDebug();
    }

    public function getColor()
    {
        return $this->player->getColor();
    }

    public function hasMoved()
    {
        return null !== $this->firstMove;
    }

    public function preMove(Square $oldSquare, Square $square, array $options = array())
    {

    }

    public function postMove(Square $oldSquare, Square $square, array $options = array())
    {

    }

    protected function getCache($key)
    {
        if(isset($this->cache[$key]))
        {
            return $this->cache[$key];
        }

        return null;
    }

    protected function hasCache($key)
    {
        return isset($this->cache[$key]);
    }

    protected function setCache($key, $value)
    {
        return $this->cache[$key] = $value;
    }

    public function clearCache($key = null)
    {
        if (null === $key)
        {
            $this->cache = array();
        }
        elseif(isset($this->cache[$key]))
        {
            unset($this->cache[$key]);
        }

        return $this;
    }

    public function serialize()
    {
        return array('hash', 'color', 'x', 'y', 'player', 'isDead', 'firstMove');
    }

}
