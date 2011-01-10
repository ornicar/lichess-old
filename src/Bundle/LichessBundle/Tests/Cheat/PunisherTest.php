<?php

namespace Bundle\LichessBundle\Cheat;

use Application\UserBundle\Document\User;

class PunisherTest extends \PHPUnit_Framework_TestCase
{
    public function testPunishUserWithNoWin()
    {
        $user = $this->createUserMock(array('setElo'));
        $user->expects($this->once())
            ->method('setElo')
            ->with(User::STARTING_ELO);
        $game = $this->createGameMock(array());
        $game->expects($this->never())
            ->method('setIsEloCanceled');
        $game->expects($this->never())
            ->method('getLoser');
        $games = array();
        $gameRepo = $this->createGameRepositoryMock(array('findCancelableByUser'));
        $gameRepo->expects($this->once())
            ->method('findCancelableByUser')
            ->with($user)
            ->will($this->returnValue($games));

        $punisher = new Punisher($gameRepo);
        $punisher->punish($user);
    }

    public function testPunishUserWithAWinWithoutEloDiff()
    {
        $user = $this->createUserMock(array('setElo'));
        $user->expects($this->once())
            ->method('setElo')
            ->with(User::STARTING_ELO);
        $loserUser = $this->createUserMock(array('setElo'));
        $loserUser->expects($this->never())
            ->method('setElo');
        $loser = $this->createPlayerMock(array('getUser', 'getEloDiff'));
        $loser->expects($this->never())
            ->method('getUser');
        $loser->expects($this->once())
            ->method('getEloDiff')
            ->will($this->returnValue(null));
        $game = $this->createGameMock(array('setIsEloCanceled', 'getLoser'));
        $game->expects($this->never())
            ->method('setIsEloCanceled');
        $game->expects($this->once())
            ->method('getLoser')
            ->will($this->returnValue($loser));
        $games = array($game);
        $gameRepo = $this->createGameRepositoryMock(array('findCancelableByUser'));
        $gameRepo->expects($this->once())
            ->method('findCancelableByUser')
            ->with($user)
            ->will($this->returnValue($games));

        $punisher = new Punisher($gameRepo);
        $punisher->punish($user);
    }

    public function testPunishUserWithAWinAndEloDiff()
    {
        $user = $this->createUserMock(array('setElo'));
        $user->expects($this->once())
            ->method('setElo')
            ->with(User::STARTING_ELO);
        $loserUser = $this->createUserMock(array('setElo'));
        $loserUser->expects($this->once())
            ->method('setElo');
        $loser = $this->createPlayerMock(array('getUser', 'getEloDiff'));
        $loser->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($loserUser));
        $loser->expects($this->once())
            ->method('getEloDiff')
            ->will($this->returnValue(-10));
        $game = $this->createGameMock(array('setIsEloCanceled', 'getLoser'));
        $game->expects($this->once())
            ->method('setIsEloCanceled')
            ->with(true);
        $game->expects($this->once())
            ->method('getLoser')
            ->will($this->returnValue($loser));
        $games = array($game);
        $gameRepo = $this->createGameRepositoryMock(array('findCancelableByUser'));
        $gameRepo->expects($this->once())
            ->method('findCancelableByUser')
            ->with($user)
            ->will($this->returnValue($games));

        $punisher = new Punisher($gameRepo);
        $punisher->punish($user);
    }

    protected function createGameRepositoryMock(array $methods)
    {
        $repository = $this->getMock('Bundle\LichessBundle\Document\GameRepository', $methods, array(), '', false);

        return $repository;
    }

    protected function createGameMock(array $methods)
    {
        $game = $this->getMock('Bundle\LichessBundle\Document\Game', $methods, array(), '', false);

        return $game;
    }

    protected function createPlayerMock(array $methods)
    {
        $game = $this->getMock('Bundle\LichessBundle\Document\Player', $methods, array(), '', false);

        return $game;
    }

    protected function createUserMock(array $methods)
    {
        $userMock = $this->getMock('Application\UserBundle\Document\User', $methods, array(), '', false);

        return $userMock;
    }
}
