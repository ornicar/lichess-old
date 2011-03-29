<?php

namespace Lichess\TranslationBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class TranslationControllerTest extends WebTestCase
{
    public function testRequestWithTranslatedLanguage()
    {
        $client = $this->createPersistentClient();
        $server = array(
            'HTTP_ACCEPT_LANGUAGE' => 'fr'
        );
        $crawler = $client->request('GET', '/', array(), array(), $server);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('#incomplete_translation')->count());
        $this->assertRegexp('/Jouer avec un ami/', $client->getResponse()->getContent());
    }

    public function testRequestWithNonTranslatedLanguage()
    {
        $client = $this->createPersistentClient();
        $server = array(
            'HTTP_ACCEPT_LANGUAGE' => 'kg'
        );
        $crawler = $client->request('GET', '/', array(), array(), $server);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('#incomplete_translation')->count());
    }

    /**
     * @dataProvider translationProvider
     */
    public function testTranslation($language, array $messages)
    {
        $translator = $this->createClient()->getContainer()->get('translator');
        $translator->setLocale($language);

        foreach ($messages as $key => $value) {
            $this->assertEquals($value, $translator->trans($key));
        }
    }

    public function translationProvider()
    {
        $container = $this->createClient()->getContainer();
        $manager = $container->get('lichess_translation.manager');

        $languages = $manager->getAvailableLanguages();

        $data = array();
        foreach ($languages as $code => $name) {
            try {
                $data[] = array($code, $manager->getMessages($code));
            } catch (\InvalidArgumentException $e) {}
        }

        return $data;
    }

    protected function createPersistentClient($cookieName = 'test')
    {
        $client = parent::createClient();
        $client->getContainer()->get('session.storage.file')->deleteFile();
        $client->getCookieJar()->set(new Cookie(session_name(), $cookieName));

        return $client;
    }
}
