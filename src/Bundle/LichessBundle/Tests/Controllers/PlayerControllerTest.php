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
}

