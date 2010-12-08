<?php

namespace Application\DoctrineUserBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserPageTest extends WebTestCase
{
    public function testUserPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'doctrine_user_user_show', array('username' => 'test-username')));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('test-username (1200)', $crawler->filter('.lichess_title')->text());
        $this->assertRegexp('/Games played/', $client->getResponse()->getContent());
        $this->assertRegexp('/No recent game at the moment/', $client->getResponse()->getContent());
    }

    public function testNonExistingUserPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'doctrine_user_user_show', array('username' => 'test-notexistingusername')));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function generateUrl($client, $route, $parameters = array())
    {
        return $client->getContainer()->get('router')->generate($route, $parameters);
    }

    public function setUp()
    {
        $client = $this->createClient();
        $user = $client->getContainer()->get('doctrine_user.repository.user')->createUserInstance();
        $user->setUsername('test-username');
        $user->setPlainPassword('test-password');
        $client->getContainer()->get('doctrine_user.object_manager')->persist($user);
        $client->getContainer()->get('doctrine_user.object_manager')->flush();
    }

    public function tearDown()
    {
        $client = $this->createClient();
        $user = $client->getContainer()->get('doctrine_user.repository.user')->findOneByUsername('test-user');
        if($user) {
            $client->getContainer()->get('doctrine_user.object_manager')->remove($user);
            $client->getContainer()->get('doctrine_user.object_manager')->flush();
        }
    }
}
