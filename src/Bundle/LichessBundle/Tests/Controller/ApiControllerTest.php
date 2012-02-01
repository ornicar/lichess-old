<?php

namespace Bundle\LichessBundle\Tests\Controller;

class ApiControllerTest extends AbstractControllerTest
{
    public function testNew()
    {
        $client1 = self::createClient();
        $client2 = self::createClient();
        $crawler = $client1->request('POST', '/api/game/new');
        $this->assertTrue($client1->getResponse()->isSuccessful());
        $response = json_decode($client1->getResponse()->getContent(), true);
        $this->assertArrayHasKey('white', $response);
        $this->assertArrayHasKey('black', $response);

        $crawler = $client1->request('GET', preg_replace('#^.+(/\w+)$#', '$1', $response['black']));
        $this->assertTrue($client1->getResponse()->isSuccessful());
        $this->assertRegexp('/Waiting for opponent/', $crawler->filter('div.lichess_overboard')->text());
        $this->assertEquals(0, $crawler->filter('div.lichess_player:contains("Waiting")')->count());

        $crawler = $client2->request('GET', preg_replace('#^.+(/\w+)$#', '$1', $response['white']));
        //var_dump($client2->getResponse()->getContent());die;
        $this->assertTrue($client2->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_opponent:contains("Anonymous")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Your turn")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(0, $crawler->filter('a.force_resignation')->count());

        $crawler = $client1->reload();
        $this->assertEquals(1, $crawler->filter('div.lichess_player:contains("Waiting")')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_player div.king.white')->count());
        $this->assertEquals(1, $crawler->filter('div.lichess_chat')->count());
        $this->assertEquals(0, $crawler->filter('a.force_resignation')->count());
    }
}
