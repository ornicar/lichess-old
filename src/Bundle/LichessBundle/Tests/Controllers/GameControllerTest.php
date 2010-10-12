<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
    public function testInviteAi()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with the machine')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Opponent: Crafty A.I.")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteAiAsBlack()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/black');
        $crawler = $client->click($crawler->selectLink('Play with the machine')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Opponent: Crafty A.I.")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.black')->count());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteFriend()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with a friend')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard input';
        $this->assertEquals(1, $crawler->filter($selector)->count());

        $inviteUrl = $crawler->filter($selector)->attr('value');
        $this->assertRegexp('#^http://.*/[\w-]{6}$#', $inviteUrl);

        $syncUrl = str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
        $this->assertRegexp('#^/sync/[\w-]{6}/white/0/[\w-]{10}$#', $syncUrl);

        $friend = $this->createClient();
        $friend->request('GET', $inviteUrl);
        $crawler = $friend->followRedirect();
        $this->assertTrue($friend->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Human opponent connected")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());

        $client->reload();
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Human opponent connected")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteFriendAsBlack()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/black');
        $crawler = $client->click($crawler->selectLink('Play with a friend')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard input';
        $this->assertEquals(1, $crawler->filter($selector)->count());

        $inviteUrl = $crawler->filter($selector)->attr('value');
        $this->assertRegexp('#^http://.*/[\w-]{6}$#', $inviteUrl);

        $syncUrl = str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
        $this->assertRegexp('#^/sync/[\w-]{6}/black/0/[\w-]{10}$#', $syncUrl);

        $friend = $this->createClient();
        $friend->request('GET', $inviteUrl);
        $crawler = $friend->followRedirect();
        $this->assertTrue($friend->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Human opponent connected")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.black')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());

        $client->reload();
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Human opponent connected")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.black')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteAnybody()
    {
        $client = $this->createClient();
        $connectionFile = $client->getContainer()->getParameter('lichess.anybody.connection_file');
        @unlink($connectionFile);
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with anybody')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard.wait_anybody';
        $this->assertEquals(1, $crawler->filter($selector)->count());

        $syncUrl = str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
        $this->assertRegexp('#^/sync/[\w-]{6}/white/0/[\w-]{10}$#', $syncUrl);

        $friend = $this->createClient();
        $crawler = $friend->request('GET', '/');
        $crawler = $friend->click($crawler->selectLink('Play with anybody')->link());
        $crawler = $friend->followRedirect();
        $crawler = $friend->followRedirect();
        $this->assertTrue($friend->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Human opponent connected")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());

        $client->reload();
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Human opponent connected")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
    }

    public function testWatchGame()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $client->click($crawler->selectLink('Play with a friend')->link());
        $crawler = $client->followRedirect();
        $inviteUrl = $crawler->filter('div.lichess_game_not_started.waiting_opponent div.lichess_overboard input')->attr('value');
        $friend = $this->createClient();
        $friend->request('GET', $inviteUrl);
        $crawler = $friend->followRedirect();
        
        $spectator = $this->createClient();
        $crawler = $spectator->request('GET', $inviteUrl);
        $this->assertTrue($spectator->getResponse()->isSuccessful());
        $this->assertRegexp('#You are viewing this game as a spectator.#', $spectator->getResponse()->getContent());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
    }
}
