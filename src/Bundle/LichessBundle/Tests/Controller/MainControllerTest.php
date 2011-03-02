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

    public function testAbout()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/about');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1:contains("About Lichess")')->count());
    }

    public function testHowManyPlayersNow()
    {
        $client = $this->createClient();
        $client->request('GET', '/how-many-players-now');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^\d+$/', (string)$client->getResponse()->getContent());
    }
}
