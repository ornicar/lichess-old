<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bundle\LichessBundle\Document\Game;

abstract class AbstractControllerTest extends WebTestCase
{
    protected function inviteFriend($color = 'white')
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with a friend')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $url = $crawler->filter('div.game_config_form form')->attr('action');
        $client->request('POST', $url, array('config' => array(
            'color' => $color,
            'variant' => Game::VARIANT_STANDARD
        )));
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());

        return array($client, $crawler);
    }

    protected function inviteAnybody($join = false)
    {
        $client = self::createClient();
        !$join && $this->clearSeekQueue($client);
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with anybody')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->filter('.submit')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        if($join) {
            $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
            $client->request('GET', $redirectUrl);
            $this->assertTrue($client->getResponse()->isRedirect());
            $crawler = $client->followRedirect();
            $this->assertTrue($client->getResponse()->isSuccessful());
            $this->assertEquals(1, $crawler->filter('div.lichess_game.lichess_player_black')->count());
        } else {
            $this->assertRegexp('/Waiting for opponent/', $client->getResponse()->getContent());
            $this->assertEquals(1, $crawler->filter('div.lichess_game_not_started.lichess_player_white')->count());
        }

        return array($client, $crawler);
    }

    protected function clearSeekQueue($client)
    {
        $client->getContainer()->get('lichess.repository.seek')->createQueryBuilder()->remove()->getQuery()->execute();
    }
}
