<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bundle\LichessBundle\Document\Game;

class PgnControllerTest extends WebTestCase
{
    public function testExportAction()
    {
        $color = 'white';
        $client = self::createClient();
        $crawler = $client->request('GET', '/start/ai');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $url = $crawler->filter('div.game_config_form form')->attr('action');
        $client->request('POST', $url, array('config' => array(
            'color' => $color,
            'variant' => Game::VARIANT_STANDARD,
            'level' => 1
        )));
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $id = preg_replace('#^.+([\w-]{12}+)$#', '$1', $client->getRequest()->getUri());
        $publicId = substr($id, 0, 8);
        $crawler = $client->request('GET', '/analyse/'.$publicId);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $site = 'http://localhost/analyse/'.$publicId;
        $date = date('Y.m.d');
        $expected = <<<EOF
[Event "Casual game"]
[Site "$site"]
[Date "$date"]
[White "Anonymous"]
[Black "Crafty level 1"]
[WhiteElo "?"]
[BlackElo "?"]
[Result "*"]
[PlyCount "0"]
[Variant "Standard"]

*
EOF;
        $this->assertEquals($expected, $crawler->filter('#pgnText')->text());
    }
}
