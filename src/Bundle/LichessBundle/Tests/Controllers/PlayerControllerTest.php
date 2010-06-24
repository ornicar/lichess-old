<?php
namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Framework\FoundationBundle\Test\WebTestCase;

class PlayerControllerTest extends WebTestCase
{
    public function testResign()
    {
        $client = $this->createClient();
        $player = $this->createPlayer($client);

        // player2 joins it
        $crawler = $client->request('GET', '/'.$player->getGame()->getHash());
        $crawler = $client->followRedirect();

        // player2 resigns
        $client->click($crawler->filter('a:contains("Resign")')->link());

        // player1 wins
        $crawler = $client->request('GET', '/'.$player->getFullHash());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished div.lichess_piece.king.white')->count());
    }

    public function testRematch()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $player = $this->createPlayer($client);
        $gameHash = $player->getGame()->getHash();
        $player1Hash = $player->getFullHash();

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $crawler = $client->followRedirect();
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player2Hash = $match[1];

        // player2 resigns
        $client->click($crawler->filter('a:contains("Resign")')->link());

        // player2 proposes a rematch
        $crawler = $client->request('GET', '/'.$player2Hash);
        $client->click($crawler->filter('a:contains("Rematch")')->link());
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player2Hash = $match[1];
        $this->assertEquals(1, $crawler->filter('div.lichess_table_wait_next', 'Waiting for your previous opponent')->count());

        // player1 joins the game
        $crawler = $client->request('GET', '/'.$player1Hash);
        $client->click($crawler->filter('a:contains("Join the game")')->link());
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_black')->count());
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());

        // player2 sees player1
        $crawler = $client->request('GET', '/'.$player2Hash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testPlay()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $player = $this->createPlayer($client);
        $gameHash = $player->getGame()->getHash();
        $player1Hash = $player->getFullHash();

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_table div.lichess_player p:contains("Waiting")')->count());
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player2Hash = $match[1];

        // player1 plays
        $crawler = $client->request('GET', '/'.$player1Hash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table div.lichess_player p:contains("Your turn")')->count());
        $client->request('POST', '/move/'.$player1Hash, array('from' => 'd2', 'to' => 'd4'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#\{"time":\d+,"possible_moves":null,"events":\[\{"type":"move","from":"d2","to":"d4"\}\]\}#', $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/'.$player1Hash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table p:contains("Waiting")')->count());

        // player 2 plays
        $crawler = $client->request('GET', '/'.$player2Hash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table p:contains("Your turn")')->count());
        $client->request('POST', '/move/'.$player2Hash, array('from' => 'e7', 'to' => 'e5'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#\{"time":\d+,"possible_moves":null,"events":\[\{"type":"move","from":"e7","to":"e5"\}\]\}#', $client->getResponse()->getContent());

        // player 1 plays and eat a pawn
        $client->request('POST', '/move/'.$player1Hash, array('from' => 'd4', 'to' => 'e5'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#\{"time":\d+,"possible_moves":null,"events":\[\{"type":"move","from":"d4","to":"e5"\}\]\}#', $client->getResponse()->getContent());
    }

    public function testPlayTimeout()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $player1 = $this->createPlayer($client);
        $gameHash = $player1->getGame()->getHash();
        $player1Hash = $player1->getFullHash();

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_table div.lichess_player p:contains("Waiting")')->count());
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player2Hash = $match[1];

        // player1 plays
        $crawler = $client->request('GET', '/'.$player1Hash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table div.lichess_player p:contains("Your turn")')->count());
        $client->request('POST', '/move/'.$player1Hash, array('from' => 'd2', 'to' => 'd4'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#\{"time":\d+,"possible_moves":null,"events":\[\{"type":"move","from":"d2","to":"d4"\}\]\}#', $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/'.$player1Hash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table p:contains("Waiting")')->count());

        // player1 disconnects
        $game = $client->getContainer()->getLichessPersistenceService()->find($player1->getGame()->getHash());
        $player1 = $game->getPlayerByHash($player1->getHash());
        $player2Hash = $player1->getOpponent()->getFullHash();
        $player1->setTime(time() - $client->getContainer()->getParameter('lichess.synchronizer.timeout') -1);
        $client->getContainer()->getLichessPersistenceService()->save($game);

        // player 2 plays
        $client->request('POST', '/move/'.$player2Hash, array('from' => 'e7', 'to' => 'e5'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#\{"time":\d+,"possible_moves":null,"events":\[\{"type":"move","from":"e7","to":"e5"\},\{"type":"end","table_url":"[^"]+"\}\]\}#', $client->getResponse()->getContent());
    }

    public function testSync()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $player1 = $this->createPlayer($client);
        $crawler = $client->request('GET', '/'.$player1->getFullHash());

        // player2 joins it
        $crawler = $client->request('GET', '/'.$player1->getGame()->getHash());
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $player2 = $player1->getOpponent();

        // player1 syncs
        $client->request('GET', '/sync/'.$player1->getFullHash());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('', $client->getResponse()->getContent());

        // player2 syncs
        $client->request('GET', '/sync/'.$player2->getFullHash());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('', $client->getResponse()->getContent());
    }

    public function testSyncTimeout()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $player1 = $this->createPlayer($client);
        $client->request('GET', '/'.$player1->getFullHash());

        // player2 joins it
        $client->request('GET', '/'.$player1->getGame()->getHash());
        $client->followRedirect();
        $player2 = $player1->getOpponent();

        // player1 disconnects
        $game = $client->getContainer()->getLichessPersistenceService()->find($player1->getGame()->getHash());
        $player1 = $game->getPlayerByHash($player1->getHash());
        $player2 = $game->getPlayerByHash($player2->getHash());
        $player1->setTime(time() - $client->getContainer()->getParameter('lichess.synchronizer.timeout') -1);
        $client->getContainer()->getLichessPersistenceService()->save($game);

        // player2 syncs
        $client->request('GET', '/sync/'.$player2->getFullHash());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $expected = array('time' => time(), 'possible_moves' => null, 'events' => array(array('type' => 'end', 'table_url' => '/table/'.$player2->getFullHash())));
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
        
        // player2 refreshes and sees the resigned game
        $crawler = $client->request('GET', '/'.$player2->getFullHash());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_black')->count());
        $this->assertRegexp('#White left the game#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
        $this->assertRegexp('#Black is victorious#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
        
        // player1 refreshes and sees the resigned game
        $crawler = $client->request('GET', '/'.$player1->getFullHash());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertRegexp('#White left the game#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
        $this->assertRegexp('#Black is victorious#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
    }

    protected function createPlayer($client)
    {
        $player = $client->getContainer()->getLichessGeneratorService()->createGameForPlayer('white');
        $client->getContainer()->getLichessSynchronizerService()->synchronize($player);
        $client->getContainer()->getLichessPersistenceService()->save($player->getGame());
        return $player;
    }
}
