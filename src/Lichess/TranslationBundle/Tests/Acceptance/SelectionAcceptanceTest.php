<?php

namespace Lichess\TranslationBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SelectionAcceptanceTest extends WebTestCase
{
    public function testListOfLocaleLinks()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/translation/switch/list');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('a:contains("arpitan")')->count());
    }
}
