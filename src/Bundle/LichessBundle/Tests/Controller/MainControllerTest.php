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

    /**
     * @dataProvider getCultures
     *
     * @param mixed $culture
     */
    public function testTranslation($culture, $gameUrl)
    {
        $client = $this->createClient();
        $client->request('GET', '/locale/'.$culture);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals($culture, $crawler->filter('html')->attr('lang'));
        $crawler = $client->request('GET', $gameUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals($culture, $crawler->filter('html')->attr('lang'));
    }

    public function getCultures()
    {
        $container = $this->createClient()->getContainer();
        $locales = $container->getParameter('lichess.locales');
        $game = $container->get('lichess.repository.game')->findOneBy(array());
        $gameUrl = '/'.$game->getId();

        $cultures = array();
        foreach ($locales as $locale => $name) {
            $cultures[] = array($locale, $gameUrl);
        }
        $cultures[] = array('en');

        return $cultures;
    }

}
