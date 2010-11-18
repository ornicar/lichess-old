<?php

namespace Bundle\LichessBundle\Tests\Controller;

class GameControllerTest extends AbstractControllerTest
{
    public function testViewCurrentGames()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/games');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(9, $crawler->filter('div.game_mini')->count());
    }

    public function testViewAllGames()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/games/all');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(10, $crawler->filter('div.game_row')->count());
    }

    public function testViewMateGames()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/games/checkmate');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $nbMates = min(10, $client->getContainer()->get('lichess.repository.game')->getNbMates());
        $this->assertEquals($nbMates, $crawler->filter('div.game_row')->count());
    }

    public function testInviteAi()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with the machine')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
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
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Opponent: Crafty A.I.")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.black')->count());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteFriend()
    {
        list($client, $crawler) = $this->inviteFriend();

        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard input';
        $this->assertEquals(1, $crawler->filter($selector)->count());

        $inviteUrl = $crawler->filter($selector)->attr('value');
        $this->assertRegexp('#^http://.*/[\w-]{8}$#', $inviteUrl);

        $syncUrl = str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
        $this->assertRegexp('#^/sync/[\w-]{8}/white/0/[\w-]{12}$#', $syncUrl);

        $friend = $this->createClient();
        $crawler = $friend->request('GET', $inviteUrl);
        $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
        $friend->request('GET', $redirectUrl);
        $this->assertTrue($friend->getResponse()->isRedirect());
        $crawler = $friend->followRedirect();
        $this->assertTrue($friend->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());

        $client->reload();
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteFriendAsBlack()
    {
        list($client, $crawler) = $this->inviteFriend('black');
        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard input';
        $this->assertEquals(1, $crawler->filter($selector)->count());

        $inviteUrl = $crawler->filter($selector)->attr('value');
        $this->assertRegexp('#^http://.*/[\w-]{8}$#', $inviteUrl);

        $syncUrl = str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
        $this->assertRegexp('#^/sync/[\w-]{8}/black/0/[\w-]{12}$#', $syncUrl);

        $friend = $this->createClient();
        $crawler = $friend->request('GET', $inviteUrl);
        $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
        $friend->request('GET', $redirectUrl);
        $this->assertTrue($friend->getResponse()->isRedirect());
        $crawler = $friend->followRedirect();
        $this->assertTrue($friend->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.black')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());

        $client->reload();
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.black')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
    }

    public function testInviteAnybody()
    {
        list($client, $crawler) = $this->inviteAnybody();
        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard.wait_anybody';
        $this->assertEquals(1, $crawler->filter($selector)->count());

        $syncUrl = str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
        $this->assertRegexp('#^/sync/[\w-]{8}/white/0/[\w-]{12}$#i', $syncUrl);

        list($friend, $crawler) = $this->inviteAnybody('black', true);
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());

        $client->reload();
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
    }

    public function testWatchGame()
    {
        list($client, $crawler) = $this->inviteFriend();

        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard input';
        $inviteUrl = $crawler->filter($selector)->attr('value');

        $friend = $this->createClient();
        $crawler = $friend->request('GET', $inviteUrl);
        $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
        $friend->request('GET', $redirectUrl);
        $crawler = $friend->followRedirect();

        $spectator = $this->createClient();
        $crawler = $spectator->request('GET', $inviteUrl);
        $this->assertTrue($spectator->getResponse()->isSuccessful());
        $this->assertRegexp('#You are viewing this game as a spectator.#', $spectator->getResponse()->getContent());
        $this->assertEquals(0, $crawler->filter('div.lichess_chat')->count());
    }
}
