<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PgnControllerTest extends WebTestCase
{
    public function testExportAction()
    {
        $client = $this->createClient();
        $client->request('GET', '/ai/white');
        $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $hash = preg_replace('#^.+([\w-]{10}+)$#', '$1', $client->getRequest()->getUri());
        $publicHash = substr($hash, 0, 6);
        $crawler = $client->request('GET', '/analyse/'.$publicHash);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $client->click($crawler->selectLink('Export PGN')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $site = 'http://localhost/analyse/'.$publicHash;
        $expected = <<<EOF
[Site "$site"]
[Result "*"]

*
EOF;
        $this->assertEquals($expected, $client->getResponse()->getContent());
    }
}
