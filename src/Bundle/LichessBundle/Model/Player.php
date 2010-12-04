<?php

namespace Bundle\LichessBundle\Model;

use Bundle\DoctrineUserBundle\Model\User;

interface Player {

    function getIsOfferingDraw();

    function setIsOfferingDraw($isOfferingDraw);

    function canOfferDraw();

    function getUser();

    function setUser(User $user = null);

    function getElo();

    function getUsername($default = 'Anonymous');

    function getUsernameWithElo($default = 'Anonymous');

    function getStack();

    function setStack($stack);

    function addEventsToStack(array $events);

    function addEventToStack(array $event);

    function getId();

    function getFullId();

    function getAiLevel();

    function setAiLevel($aiLevel);

    function getKing();

    function getPiecesByClass($class);

    function getNbAlivePieces();

    function getDeadPieces();

    function getIsAi();

    function getIsHuman();

    function setIsAi($isAi);

    function getIsWinner();

    function setIsWinner($isWinner);

    function getPieces();

    function setPieces(array $pieces);

    function addPiece(Piece $piece);

    function removePiece(Piece $piece);

    function getGame();

    function setGame(Game $game);

    function getColor();

    function getOpponent();

    function getIsMyTurn();

    function isWhite();

    function isBlack();

    function __toString();

    function isMyTurn();

    function getBoard();
}