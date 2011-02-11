<?php

namespace Application\UserBundle\Tests\Acceptance;

class UserPageAcceptanceTest extends AbstractAcceptanceTest
{
    protected $username = 'user1';
    protected $userPass = 'password1';

    public function testUserPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_show', array('username' => $this->username)));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^'.$this->username.'/', $crawler->filter('.lichess_title')->text());
        $this->assertRegexp('/Games played/', $client->getResponse()->getContent());
        $this->assertRegexp('/No recent game at the moment/', $client->getResponse()->getContent());
    }

    public function testNonExistingUserPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_show', array('username' => 'test-notexistingusername')));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }
}
