<?php

namespace Bundle\LichessBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TranslationControllerTest extends WebTestCase
{
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
        $manager = $container->get('lichess.translation.manager');

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
