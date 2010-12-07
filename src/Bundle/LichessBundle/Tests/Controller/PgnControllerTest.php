<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PgnControllerTest extends WebTestCase
{
    public function testExportAction()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/ai/white');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
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
[Result "*"]
[Variant "Standard"]
[FEN "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq"]

*
EOF;
        $this->assertEquals($expected, $crawler->filter('#pgnText')->text());
    }
}
