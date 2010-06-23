<?php
namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Framework\WebBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("Lichess")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_game')->count());
        $this->assertEquals(3, $crawler->filter('a.lichess_button')->count());
    }

    public function testChangeColor()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertEquals(1, $crawler->filter('div.lichess_player_white')->count());

        $crawler = $client->click($crawler->filter('a.lichess_exchange')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('div.lichess_player_black')->count());
    }

    public function testAbout()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/about');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("About Lichess")')->count());
    }
}

