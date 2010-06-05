<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Entities as Entities;

require_once __DIR__.'/../gameBootstrap.php';

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $generator = new Generator();
        $this->assertEquals('Bundle\LichessBundle\Chess\Generator', get_class($generator));
    }

    public function testGameCreation()
    {
        $generator = new Generator();

        $game = $generator->createGame();

        $this->assertTrue($game instanceof Entities\Game);
        $this->assertEquals(0, $game->getTurns());
        $this->assertEquals(false, $game->getIsStarted());
        $this->assertEquals(false, $game->getIsFinished());

        return $game;
    }

    /**
     * @depends testGameCreation
     */
    public function testGamePlayers(Entities\Game $game)
    {
        $this->assertEquals(2, count($game->getPlayers()));

        $this->assertEquals(array('white', 'black'), array_keys($game->getPlayers()));

        $player = $game->getPlayer('white');
        $this->assertTrue($player instanceof Entities\Player);
        $this->assertEquals('white', $player->getColor());
        $this->assertEquals(false, $player->getIsWinner());
        $this->assertEquals(false, $player->getIsAi());
        $this->assertSame($game, $player->getGame());
        $this->assertSame($game->getPlayer('black'), $player->getOpponent());
        $this->assertSame($player, $player->getOpponent()->getOpponent());
    }

    /**
     * @depends testGameCreation
     */
    public function testGamePlayerTurn(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $this->assertTrue($player->getIsMyTurn());
        $this->assertFalse($player->getOpponent()->getIsMyTurn());

        $game->setTurns($game->getTurns()+1);
        $this->assertFalse($player->getIsMyTurn());
        $this->assertTrue($player->getOpponent()->getIsMyTurn());
    }

    /**
     * @depends testGameCreation
     */
    public function testGameGetWinner(Entities\Game $game)
    {
        $this->assertNull($game->getWinner());

        $game->getPlayer('white')->setIsWinner(true);
        $this->assertSame($game->getPlayer('white'), $game->getWinner());
    }

    /**
     * @depends testGameCreation
     */
    public function testGamePieces(Entities\Game $game)
    {
        $this->assertEquals(32, count($game->getPieces()));
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerPieces(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $this->assertEquals(16, count($player->getPieces()));
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerKing(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $king = $player->getKing();
        $this->assertTrue($king instanceof Entities\Piece\King);
        $this->assertEquals(1, $king->getY());
        $this->assertSame($player, $king->getPlayer());
        $this->assertTrue($king->isClass('King'));
        $this->assertFalse($king->getIsDead());
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerPawns(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $pawns = $player->getPiecesByClass('Pawn');
        $this->assertTrue($pawns[0] instanceof Entities\Piece\Pawn);
        $this->assertEquals(2, $pawns[0]->getY());
        $this->assertEquals(8, count($pawns));
        $this->assertSame($player, $pawns[0]->getPlayer());
        $this->assertTrue($pawns[0]->isClass('Pawn'));
        $this->assertFalse($pawns[0]->getIsDead());
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerRooks(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $rooks = $player->getPiecesByClass('Rook');
        $this->assertTrue($rooks[0] instanceof Entities\Piece\Rook);
        $this->assertEquals(1, $rooks[0]->getY());
        $this->assertEquals(2, count($rooks));
        $this->assertSame($player, $rooks[0]->getPlayer());
        $this->assertTrue($rooks[0]->isClass('Rook'));
        $this->assertFalse($rooks[0]->getIsDead());
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerKnights(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $knights = $player->getPiecesByClass('Knight');
        $this->assertTrue($knights[0] instanceof Entities\Piece\Knight);
        $this->assertEquals(1, $knights[0]->getY());
        $this->assertEquals(2, count($knights));
        $this->assertSame($player, $knights[0]->getPlayer());
        $this->assertTrue($knights[0]->isClass('Knight'));
        $this->assertFalse($knights[0]->getIsDead());
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerBishops(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $bishops = $player->getPiecesByClass('Bishop');
        $this->assertTrue($bishops[0] instanceof Entities\Piece\Bishop);
        $this->assertEquals(1, $bishops[0]->getY());
        $this->assertEquals(2, count($bishops));
        $this->assertSame($player, $bishops[0]->getPlayer());
        $this->assertTrue($bishops[0]->isClass('Bishop'));
        $this->assertFalse($bishops[0]->getIsDead());
    }

    /**
     * @depends testGameCreation
     */
    public function testPlayerQueens(Entities\Game $game)
    {
        $player = $game->getPlayer('white');
        $queens = $player->getPiecesByClass('Queen');
        $this->assertTrue($queens[0] instanceof Entities\Piece\Queen);
        $this->assertEquals(1, $queens[0]->getY());
        $this->assertEquals(1, count($queens));
        $this->assertSame($player, $queens[0]->getPlayer());
        $this->assertTrue($queens[0]->isClass('Queen'));
        $this->assertFalse($queens[0]->getIsDead());
    }
}
