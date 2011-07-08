<?php

namespace Lichess\TranslationBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class TranslationAcceptanceTest extends WebTestCase
{
    /**
     * @dataProvider translationProvider
     */
    public function testTranslation($language, array $messages)
    {
        $translator = self::createClient()->getContainer()->get('translator');
        $translator->setLocale($language);

        foreach ($messages as $key => $value) {
            $this->assertEquals($value, $translator->trans($key));
        }
    }

    public function translationProvider()
    {
        $container = self::createClient()->getContainer();
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
}
