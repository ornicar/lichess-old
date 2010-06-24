<?php
namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Framework\FoundationBundle\Test\WebTestCase;

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
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());

        // player1 is redirected to its player page
        $crawler = $client->request('GET', '/'.$playerHash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testPlayWithFriendTimeout()
    {
        $client = $this->createClient();
        @unlink($client->getContainer()->getParameter('lichess.anybody.connection_file'));

        // player1 creates a new game
        $crawler = $client->request('GET', '/');
        $gameUrl = $crawler->filter('div.lichess_join_url span')->text();
        $gameHash = substr($gameUrl, -6);
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player1Hash = $match[1];

        // player1 disconnects
        $game = $client->getContainer()->getLichessPersistenceService()->find($gameHash);
        $player1 = $game->getPlayerByHash(substr($player1Hash, 6, 4));
        $player1->setTime(time() - $client->getContainer()->getParameter('lichess.synchronizer.timeout') -1);
        $client->getContainer()->getLichessPersistenceService()->save($game);

        // player2 joins the game
        $crawler = $client->request('GET', '/'.$gameHash);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_black')->count());
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());
        $this->assertRegexp('#White left the game#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
        $this->assertRegexp('#Black is victorious#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());

        // player1 comes back
        $crawler = $client->request('GET', '/'.$player1->getFullHash());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table.finished')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());
        $this->assertRegexp('#White left the game#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
        $this->assertRegexp('#Black is victorious#s', $crawler->filter('div.lichess_table div.lichess_player p')->text());
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
        $this->assertRegexp('#Human opponent#', $crawler->filter('div.lichess_opponent span')->text());
    }

    public function testPlayWithAnybodyTimeout()
    {
        $client = $this->createClient();
        @unlink($client->getContainer()->getParameter('lichess.anybody.connection_file'));

        // player1 creates a new game
        $crawler = $client->request('GET', '/');
        preg_match('#"player":\{"fullHash":"([\w\d]{10})"#', $client->getResponse()->getContent(), $match);
        $player1Hash = $match[1];

        // player1 invites anybody
        $crawler = $client->click($crawler->filter('a:contains("Play with anybody")')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table_wait_anybody')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());

        // player1 disconnects
        $game = $client->getContainer()->getLichessPersistenceService()->find(substr($player1Hash, 0, 6));
        $player1 = $game->getPlayerByHash(substr($player1Hash, 6, 4));
        $player1->setTime(time() - $client->getContainer()->getParameter('lichess.synchronizer.timeout') -1);
        $client->getContainer()->getLichessPersistenceService()->save($game);

        // player2 creates a new game
        $crawler = $client->request('GET', '/');

        // player2 invites anybody
        $crawler = $client->click($crawler->filter('a:contains("Play with anybody")')->link());
        //$this->assertRoute('lichess_anybody', $client);
        $this->assertFalse($client->getResponse()->isRedirection());

        // player2 sees the wait anybody page
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_board')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table_wait_anybody')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());
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
        $this->assertEquals(410, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("This game has 2 players")')->count());
    }

    protected function assertRoute($route, $client)
    {
        $profiler = $this->getProfiler($client->getResponse());
        $this->assertEquals($route, $profiler['app']->getRoute());
    }
}

