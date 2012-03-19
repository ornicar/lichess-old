<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bundle\LichessBundle\Document\Game;

class PlayerWithAiControllerTest extends WebTestCase
{
    public function testStartWithAi($color = 'white')
    {
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
        $client = self::createClient();
        $syncUrl = $this->getSyncUrl($id);

        $client->request('POST', $syncUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $nbActivePlayers = $client->getContainer()->get('lila')->nbPlayers();
        $this->assertEquals('{"v":0,"oa":2,"e":[],"p":"white","t":0}', $client->getResponse()->getContent());
    }

    /**
     * @depends testStartWithAi
     */
    public function testMoveWithAi($id)
    {
        $client = self::createClient();
        $moveUrl = $this->getMoveUrl($id);

        $client->request('POST', $moveUrl, array('from' => 'b1', 'to' => 'c3'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $nbActivePlayers = $client->getContainer()->get('lila')->nbPlayers();
        $this->assertEquals('ok', $client->getResponse()->getContent());

        $client->request('POST', $this->getSyncUrl($id));
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(5, $response['v']);
        $this->assertEquals(2, $response['oa']);
        $this->assertEquals('white', $response['p']);
        $this->assertEquals(2, $response['t']);

        return $id;
    }

    /**
     * @depends testMoveWithAi
     */
    public function testReSyncWithAi($id)
    {
        $client = self::createClient();
        $syncUrl = $this->getSyncUrl($id);

        $client->request('POST', $syncUrl);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('#^\{"v":5,"oa":2,"e":\[.+\],"p":"(white|black)","t":\d+}$#', $client->getResponse()->getContent());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testIllegalMoveWithAi($id)
    {
        $client = self::createClient();
        $moveUrl = $this->getMoveUrl($id);

        $client->request('POST', $moveUrl, array('from' => 'a1', 'to' => 'a8'));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testResign($id)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $id);

        $client->click($crawler->selectLink('Resign')->link());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_current_player p:contains("White resigned")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_table a:contains("New opponent")')->count());
    }

    /**
     * @depends testMoveWithAi
     */
    public function testReplay($id)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $id);

        $crawler = $client->click($crawler->selectLink('Replay and analyse')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h1:contains("Replay and analyse")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Flip board")')->count());
        $this->assertEquals(1, $crawler->filter('textarea#pgnText')->count());
    }

    protected function getSyncUrl($id)
    {
        $client = self::createClient();
        $client->request('GET', $id);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getMoveUrl($id)
    {
        $client = self::createClient();
        $client->request('GET', $id);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"move":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getChangeLevelUrl($id)
    {
        $client = self::createClient();
        $client->request('GET', $id);
        return str_replace('\\', '', preg_replace('#.+"ai_level":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }
}
