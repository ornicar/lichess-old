<?php

namespace Lichess\Tests\Functional;

use Bundle\LichessBundle\Functional;

class inviteAFriendTest extends \WebTestCase
{
    public function testInvite()
    {
        $crawler = $this->client->request('GET', '/');
        $crawler = $this->client->click($crawler->filter('div.lichess_join_url span a')->link());
        
        $this->addRequestTester();
        $this->client->assertRequestParameter('_route', 'lichess_game');
        $this->client->assertRequestParameter('_controller', 'LichessBundle:Game:show');

        $this->client->followRedirect();
        
        $this->addRequestTester();
        $this->client->assertRequestParameter('_route', 'lichess_player');
        $this->client->assertRequestParameter('_controller', 'LichessBundle:Player:show');

        $this->addResponseTester();
        $this->client->assertResponseSelectEquals('#logo', array('_text'), array('Lichess'));
        $this->client->assertResponseSelectCount('.lichess_piece.king.white', 1);
        $this->client->assertResponseSelectCount('.lichess_piece.king.black', 1);
        $this->client->assertResponseSelectCount('.lichess_piece', 32);
        $this->client->assertResponseSelectCount('.lichess_square', 64);
        $this->client->assertResponseSelectCount('.lichess_square', 64);
        $this->client->assertResponseSelectCount('.lichess_game.lichess_player_black', 1);
    }
}
