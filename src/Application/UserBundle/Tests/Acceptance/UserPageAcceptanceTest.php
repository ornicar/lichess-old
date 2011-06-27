<?php

namespace Application\UserBundle\Tests\Acceptance;

class UserPageAcceptanceTest extends AbstractAcceptanceTest
{
    public function testUserPage()
    {
        list($client, $crawler) = $this->requestUserPage('user1');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^user1/', $crawler->filter('.lichess_title')->text());
        $this->assertRegexp('/Games played/', $client->getResponse()->getContent());
    }

    public function testNoGamePlayed()
    {
        list($client, $crawler) = $this->requestUserPage('user4');
        $this->assertRegexp('/No recent game at the moment/', $client->getResponse()->getContent());
    }

    public function testSomeGamesPlayed()
    {
        list($client, $crawler) = $this->requestUserPage('user1');
        $this->assertGreaterThan(3, $crawler->filter('div.game_row')->count());
    }

    public function testNonExistingUserPage()
    {
        list($client, $crawler) = $this->requestUserPage('test-notexistingusername');
        $this->assertRegexp('/does not exist/', $client->getResponse()->getContent());
    }

    protected function requestUserPage($username)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_show', array('username' => $username)));

        return array($client, $crawler);
    }
}
