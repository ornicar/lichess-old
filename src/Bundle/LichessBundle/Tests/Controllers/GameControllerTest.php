<?php
namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Framework\WebBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
    public function testShow()
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

        // player1 is redirected to its player page
        $crawler = $client->request('GET', '/'.$playerHash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
    }
}

