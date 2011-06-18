<?php

namespace Lichess\TranslationBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContributionAcceptanceTest extends WebTestCase
{
    public function testIndex()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/translation/contribute');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertGreaterThan(50, $crawler->filter('#lichess_translation_form_code option')->count());
    }

    public function testLocale()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/translation/contribute/fr');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegExp('/'.preg_quote('français - 100%', '/').'/', $client->getResponse()->getContent());
    }

    public function testSubmitTranslation()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/translation/contribute/fr');
        $client->submit($crawler->selectButton('Submit translations')->form());
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegExp('/'.preg_quote('Your translation has been submitted', '/').'/', $client->getResponse()->getContent());
    }
}
