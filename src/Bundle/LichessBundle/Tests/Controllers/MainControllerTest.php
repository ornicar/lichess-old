<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndexDefaultsToWhite()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_game_not_started.lichess_player_white')->count());
    }

    public function testIndexSwitchToBlack()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.lichess_exchange')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_game_not_started.lichess_player_black')->count());
    }
}
