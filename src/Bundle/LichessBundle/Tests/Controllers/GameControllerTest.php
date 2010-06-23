<?php
namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Framework\WebBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
    public function testPlayWithFriend()
    {
        // player1 creates a new game
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $playerHash = $match[1];
        $this->assertEquals(0, strncmp($gameHash, $playerHash, 6));

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());

        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_black')->count());
        $this->assertEquals('Human opponent', $crawler->filter('div.lichess_opponent span')->text());

        // player1 is redirected to its player page
        $crawler = $client->request('GET', '/'.$playerHash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertEquals('Human opponent', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testPlayWithAi()
    {
        // player1 creates a new game
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        // player1 invites ai
        $crawler = $client->click($crawler->filter('a:contains("Play with the machine")')->link());
        $this->assertTrue($client->getResponse()->isRedirection());

        // player1 is redirected to its player page
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertEquals('Opponent is Crafty A.I.', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testPlayWithAnybody()
    {
        $client = $this->createClient();
        @unlink($client->getContainer()->getParameter('lichess.anybody.connection_file'));

        // player1 creates a new game
        $crawler = $client->request('GET', '/');

        // player1 invites anybody
        $crawler = $client->click($crawler->filter('a:contains("Play with anybody")')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table_wait_anybody')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());

        // player2 creates a new game
        $crawler = $client->request('GET', '/');

        // player2 invites anybody
        $crawler = $client->click($crawler->filter('a:contains("Play with anybody")')->link());
        $this->assertTrue($client->getResponse()->isRedirection());

        // player2 is redirected to the game page
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isRedirection());

        // player2 is redirected to its player page
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_black')->count());
        $this->assertEquals('Human opponent', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testJoinStartedGame()
    {
        // player1 creates a new game
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);

        // player2 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());

        // player3 joins it
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("This game has 2 players")')->count());
    }
}

