<?php
namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Framework\WebBundle\Test\WebTestCase;

class PlayerControllerTest extends WebTestCase
{
    public function testResign()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $crawler = $client->request('GET', '/');

        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $playerHash = $match[1];

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $crawler = $client->followRedirect();

        // player2 resigns
        $client->click($crawler->filter('a:contains("Resign")')->link());

        // player1 wins
        $crawler = $client->request('GET', '/'.$playerHash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished div.lichess_piece.king.white')->count());
    }

    public function testRematch()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $crawler = $client->request('GET', '/');

        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player1Hash = $match[1];

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
        $this->assertEquals('Human opponent', $crawler->filter('div.lichess_opponent span')->text());

        // player2 sees player1
        $crawler = $client->request('GET', '/'.$player2Hash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertEquals('Human opponent', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testPlay()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $crawler = $client->request('GET', '/');

        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player1Hash = $match[1];

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player2Hash = $match[1];

        // player1 plays
        $crawler = $client->request('GET', '/'.$player1Hash);
        $this->assertEquals(1, $crawler->filter('div.lichess_table p:contains("Your turn")')->count());
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

    public function testSync()
    {
        $client = $this->createClient();
        // player1 creates a new game
        $crawler = $client->request('GET', '/');

        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player1Hash = $match[1];

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player2Hash = $match[1];

        // player1 syncs
        $client->request('GET', '/sync/'.$player1Hash);
        $this->assertEquals('', $client->getResponse()->getContent());
    }
}

