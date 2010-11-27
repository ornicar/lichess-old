<?php

namespace Bundle\LichessBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected function inviteFriend($color = 'white')
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/'.$color);
        $crawler = $client->click($crawler->selectLink('Play with a friend')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());

        return array($client, $crawler);
    }

    protected function inviteAnybody($color = 'white', $join = false)
    {
        $client = $this->createClient();
        !$join && $client->getContainer()->get('lichess.repository.seek')->createQueryBuilder()->remove()->getQuery()->execute();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with anybody')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        if($join) {
            $this->assertTrue($client->getResponse()->isSuccessful());
            $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
            $client->request('GET', $redirectUrl);
            $this->assertTrue($client->getResponse()->isRedirect());
            $crawler = $client->followRedirect();
        }
        $this->assertTrue($client->getResponse()->isSuccessful());

        return array($client, $crawler);
    }
}
