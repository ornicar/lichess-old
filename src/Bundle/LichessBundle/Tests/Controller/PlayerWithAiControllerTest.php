<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayerWithAiControllerTest extends WebTestCase
{
    public function testStartWithAi()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/ai/white');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $id = preg_replace('#^.+([\w-]{12}+)$#', '$1', $client->getRequest()->getUri());

        return $id;
    }

    /**
     * @depends testStartWithAi
     */
    public function testSyncWithAi($id)
    {
        $client = $this->createClient();
        $syncUrl = $this->getSyncUrl($id);

        $client->request('GET', $syncUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $nbConnectedPlayers = $client->getContainer()->get('lichess_synchronizer')->getNbConnectedPlayers();
        $this->assertEquals('{"v":0,"o":true,"e":[],"p":"white","t":0,"ncp":'.$nbConnectedPlayers.'}', $client->getResponse()->getContent());
    }

    /**
     * @depends testStartWithAi
     */
    public function testMoveWithAi($id)
    {
        $client = $this->createClient();
        $moveUrl = $this->getMoveUrl($id);

        $client->request('POST', $moveUrl, array('from' => 'b1', 'to' => 'c3'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $nbConnectedPlayers = $client->getContainer()->get('lichess_synchronizer')->getNbConnectedPlayers();
        $this->assertEquals('{"v":2,"o":true,"e":[{"type":"move","from":"b1","to":"c3"},{"type":"possible_moves","possible_moves":null}],"p":"black","t":1,"ncp":'.$nbConnectedPlayers.'}', $client->getResponse()->getContent());

        return $id;
    }

    /**
     * @depends testMoveWithAi
     */
    public function testReSyncWithAi($id)
    {
        $client = $this->createClient();
        $syncUrl = $this->getSyncUrl($id);

        $client->request('GET', $syncUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#^\{"v":4,"o":true,"e":\[.+\],"p":"(white|black)","t":\d+,"ncp":\d+\}$#', $client->getResponse()->getContent());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testIllegalMoveWithAi($id)
    {
        $client = $this->createClient();
        $moveUrl = $this->getMoveUrl($id);

        $client->request('POST', $moveUrl, array('from' => 'a1', 'to' => 'a8'));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testChangeAiLevelValid($id)
    {
        $client = $this->createClient();
        $changeLevelUrl = $this->getChangeLevelUrl($id);

        $client->request('POST', $changeLevelUrl, array('level' => 3));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('done', $client->getResponse()->getContent());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testResign($id)
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $id);

        $client->click($crawler->selectLink('Resign')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_current_player p:contains("White resigned")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table a:contains("New game")')->count());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testReplay($id)
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $id);

        $crawler = $client->click($crawler->selectLink('Replay and analyse')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1:contains("Replay and analyse")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Flip board")')->count());
        $this->assertEquals(1, $crawler->filter('textarea#pgnText')->count());
    }

    protected function getSyncUrl($id)
    {
        $client = $this->createClient();
        $client->request('GET', $id);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getMoveUrl($id)
    {
        $client = $this->createClient();
        $client->request('GET', $id);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"move":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getChangeLevelUrl($id)
    {
        $client = $this->createClient();
        $client->request('GET', $id);
        return str_replace('\\', '', preg_replace('#.+"ai_level":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }
}
