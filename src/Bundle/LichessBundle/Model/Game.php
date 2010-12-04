<?php

namespace Bundle\LichessBundle\Model;

use Bundle\DoctrineUserBundle\Model\User;

interface Game {

    function getId();

    function addUserId($userId);

    function setWinner(Player $player);

    function getInitialFen();

    function setInitialFen($fen);

    function getVariant();

    function setVariant($variant);

    function isStandardVariant();

    function getVariantName();

    static function getVariantNames();

    function getClock();

    function setClock(Clock $clock = null);

    function setClockTime($time);

    function hasClock();

    function getClockMinutes();

    function getClockName();

    function checkOutOfTime();

    function addPositionHash();

    function clearPositionHashes();

    function isThreefoldRepetition();

    function getHalfmoveClock();

    function getFullmoveNumber();

    function isCandidateToAutoDraw();

    function getPgnMoves();

    function setPgnMoves(array $pgnMoves);

    function addPgnMove($pgnMove);

    function getNext();

    function setNext($next);

    function getStatus();

    function getStatusMessage();

    function setStatus($status);

    function start();

    function getRoom();

    function hasRoom();

    function setRoom($room);

    function addRoomMessage($author, $message);

    function getBoard();

    function setBoard($board);

    function getIsFinished();

    function getIsStarted();

    function getIsTimeOut();

    function getPlayers();

    function getPlayer($color);
    
    function getPlayerById($id);

    function getPlayerByUser(User $user = null);

    function getPlayerByUserOrCreator(User $user = null);

    function getTurnPlayer();

    function getTurnColor();

    function getCreatorColor();

    function setCreatorColor($creatorColor);

    function getCreator();

    function getInvited();

    function setCreator(Player $player);

    function getWinner();

    function addPlayer(Player $player);

    function getTurns();

    function setTurns($turns);

    function addTurn();

    function getPieces();

    function getUpdatedAt();

    function setUpdatedAt(\DateTime $updatedAt);

    function isBeingPlayed();

    function getCreatedAt();

    function setCreatedAt(\DateTime $createdAt);

    function __toString();
}