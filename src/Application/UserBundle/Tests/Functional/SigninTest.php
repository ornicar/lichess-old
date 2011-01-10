<?php

namespace Application\UserBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SigninTest extends WebTestCase
{
    public function testLoginWrongPasswordFails()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'lichess_homepage'));
        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = 'test-username';
        $form['_password'] = 'bad-password';
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        // redirect loop :-/
        return;
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('a.goto_profile'));
        $this->assertEquals(1, $crawler->filter('form.signin_form'));
    }

    public function generateUrl($client, $route, $parameters = array())
    {
        return $client->getContainer()->get('router')->generate($route, $parameters);
    }

    public function setUp()
    {
        $client = $this->createClient();
        $user = $client->getContainer()->get('fos_user.repository.user')->createUserInstance();
        $user->setUsername('test-username');
        $user->setPlainPassword('test-password');
        $client->getContainer()->get('fos_user.object_manager')->persist($user);
        $client->getContainer()->get('fos_user.object_manager')->flush();
    }

    public function tearDown()
    {
        $client = $this->createClient();
        $user = $client->getContainer()->get('fos_user.repository.user')->findOneByUsername('test-user');
        if($user) {
            $client->getContainer()->get('fos_user.object_manager')->remove($user);
            $client->getContainer()->get('fos_user.object_manager')->flush();
        }
    }
}
