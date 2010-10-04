<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayerWithAiControllerTest extends WebTestCase
{
    public function testStartWithAi()
    {
        $client = $this->createClient();
        $client->request('GET', '/ai/white');
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $hash = preg_replace('#^.+([\w\d]{10}+)$#', '$1', $client->getRequest()->getUri());

        return $hash;
    }

    /**
     * @depends testStartWithAi
     */
    public function testSyncWithAi($hash)
    {
        $client = $this->createClient();
        $syncUrl = $this->getSyncUrl($hash);

        $client->request('GET', $syncUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('{"v":0,"o":true,"e":[]}', $client->getResponse()->getContent());
    }

    /**
     * @depends testStartWithAi
     */
    public function testMoveWithAi($hash)
    {
        $client = $this->createClient();
        $moveUrl = $this->getMoveUrl($hash);

        $client->request('POST', $moveUrl, array('from' => 'b1', 'to' => 'c3'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('{"v":2,"o":true,"e":[{"type":"move","from":"b1","to":"c3"},{"type":"possible_moves","possible_moves":null}]}', $client->getResponse()->getContent());

        return $hash;
    }

    /**
     * @depends testMoveWithAi
     */
    public function testReSyncWithAi($hash)
    {
        $client = $this->createClient();
        $syncUrl = $this->getSyncUrl($hash);

        $client->request('GET', $syncUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#^\{"v":4,"o":true,"e":\[.+\]\}$#', $client->getResponse()->getContent());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testIllegalMoveWithAi($hash)
    {
        $client = $this->createClient();
        $moveUrl = $this->getMoveUrl($hash);

        $client->request('POST', $moveUrl, array('from' => 'a1', 'to' => 'a8'));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testChangeAiLevelValid($hash)
    {
        $client = $this->createClient();
        $changeLevelUrl = $this->getChangeLevelUrl($hash);

        $client->request('POST', $changeLevelUrl, array('level' => 3));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('done', $client->getResponse()->getContent());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testResign($hash)
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $hash);

        $client->click($crawler->selectLink('Resign')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_current_player p:contains("White resigned")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table a:contains("New game")')->count());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testReplay($hash)
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $hash);

        $crawler = $client->click($crawler->selectLink('Replay and analyse')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1:contains("Replay and analyse")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Flip board")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Export PGN")')->count());
    }

    protected function getSyncUrl($hash)
    {
        $client = $this->createClient();
        $client->request('GET', $hash);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getMoveUrl($hash)
    {
        $client = $this->createClient();
        $client->request('GET', $hash);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"move":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getChangeLevelUrl($hash)
    {
        $client = $this->createClient();
        $client->request('GET', $hash);
        return str_replace('\\', '', preg_replace('#.+"ai_level":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }
}
