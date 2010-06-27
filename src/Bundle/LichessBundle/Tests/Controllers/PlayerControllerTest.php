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
        $client->request('POST', '/resign/'.$player->getOpponent()->getFullHash().'/0');
        $this->assertEquals(array('v' => 1, 'o' => true, 'e'=>array(array('type' => 'end'))), json_decode($client->getResponse()->getContent(), true));

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
        $client->request('POST', '/resign/'.$player2Hash.'/0');

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
        $this->assertRegexp('#Human opponent connected#', $crawler->filter('div.lichess_opponent div')->text());

        // player2 sees player1
        $crawler = $client->request('GET', '/'.$player2Hash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertRegexp('#Human opponent connected#', $crawler->filter('div.lichess_opponent div')->text());
    }

    protected function createPlayer($client)
    {
        $player = $client->getContainer()->getLichessGeneratorService()->createGameForPlayer('white');
        $client->getContainer()->getLichessSynchronizerService()->setAlive($player);
        $client->getContainer()->getLichessPersistenceService()->save($player->getGame());
        return $player;
    }
}
