<?php

namespace Lichess\Tests\Functional;

use Bundle\LichessBundle\Functional;

class startAGameTest extends \WebTestCase
{
    public function testArriveOnHomepage()
    {
        $crawler = $this->client->request('GET', '/');
        
        $this->addRequestTester();
        $this->client->assertRequestParameter('_route', 'lichess_homepage');
        $this->client->assertRequestParameter('_controller', 'LichessBundle:Main:index');

        $this->addResponseTester();
        $this->client->assertResponseSelectEquals('#logo', array('_text'), array('Lichess'));
        $this->client->assertResponseSelectCount('.lichess_piece.king.white', 1);
        $this->client->assertResponseSelectCount('.lichess_piece.king.black', 1);
        $this->client->assertResponseSelectCount('.lichess_piece', 32);
        $this->client->assertResponseSelectCount('.lichess_square', 64);
        $this->client->assertResponseSelectCount('.lichess_square', 64);
        $this->client->assertResponseSelectCount('.lichess_game.lichess_player_white', 1);
    }

    public function testExchangePosition()
    {
        $crawler = $this->client->request('GET', '/');

        $crawler = $this->client->click($crawler->filter('.lichess_exchange')->link());
        
        $this->addRequestTester();
        $this->client->assertRequestParameter('_route', 'lichess_homepage');
        $this->client->assertRequestParameter('_controller', 'LichessBundle:Main:index');

        $this->addResponseTester();
        $this->client->assertResponseSelectCount('.lichess_game.lichess_player_black', 1);

        $crawler = $this->client->click($crawler->filter('.lichess_exchange')->link());
        
        $this->addRequestTester();
        $this->client->assertRequestParameter('_route', 'lichess_homepage');
        $this->client->assertRequestParameter('_controller', 'LichessBundle:Main:index');

        $this->addResponseTester();
        $this->client->assertResponseSelectCount('.lichess_game.lichess_player_white', 1);
    }
}
