<?php

namespace Bundle\LichessBundle\Model;

use Bundle\LichessBundle\Chess\Board;

interface Piece {

    function getBasicTargetKeys();

    function getAttackTargetKeys();

    function getClass();

    function getFirstMove();

    function setFirstMove($firstMove);

    function getIsDead();

    function setIsDead($isDead);

    function isClass($class);

    function getY();

    function setY($y);

    function getX();

    function setX($x);

    function getPlayer();

    function setPlayer(Player $player);

    function getSquare();

    function getSquareKey();

    function getGame();

    function getBoard();

    function setBoard(Board $board);

    function getColor();

    function hasMoved();

    function toDebug();

    function __toString();

    function getForsyth();

    function getPgn();

    function getContextualHash();
}