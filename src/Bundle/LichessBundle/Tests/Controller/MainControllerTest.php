<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndexDefaultsToWhite()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testHowManyPlayersNow()
    {
        $client = self::createClient();
        $client->request('GET', '/how-many-players-now');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^\d+$/', (string)$client->getResponse()->getContent());
    }

}
