<?php

namespace Application\UserBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SignupTest extends WebTestCase
{
    public function testSignup()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'lichess_homepage'));
        $crawler = $client->click($crawler->filter('a:contains("Sign up")')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/Sign up/', $crawler->filter('.lichess_title')->text());
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_user_form[username]'] = 'test-username';
        $form['fos_user_user_form[plainPassword]'] = 'test-password';
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_show', array('username' => 'test-username')));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/test-username/', $crawler->filter('.lichess_title')->text());
    }

    public function testSignupWithBadUsername()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_new'));
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_user_form[username]'] = 'x';
        $form['fos_user_user_form[plainPassword]'] = 'test-password';
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/username is too short/', $client->getResponse()->getContent());
    }

    public function testSignupWithBadPassword()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_new'));
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_user_form[username]'] = 'test-username';
        $form['fos_user_user_form[plainPassword]'] = '';
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/enter a password/', $client->getResponse()->getContent());
    }

    public function testSignupWithExistingUsername()
    {
        $client = $this->createClient();
        $user = $client->getContainer()->get('fos_user.repository.user')->createUserInstance();
        $user->setUsername('test-username');
        $user->setPlainPassword('test-password');
        $client->getContainer()->get('fos_user.object_manager')->persist($user);
        $client->getContainer()->get('fos_user.object_manager')->flush();

        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_new'));
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_user_form[username]'] = 'test-username';
        $form['fos_user_user_form[plainPassword]'] = 'other-password';
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/username was already taken/', $client->getResponse()->getContent());
    }

    public function generateUrl($client, $route, $parameters = array())
    {
        return $client->getContainer()->get('router')->generate($route, $parameters);
    }

    public function setUp()
    {
        $client = $this->createClient();
        $user = $client->getContainer()->get('fos_user.repository.user')->findOneByUsername('test-username');
        if($user) {
            $client->getContainer()->get('fos_user.object_manager')->remove($user);
            $client->getContainer()->get('fos_user.object_manager')->flush();
        }
    }
}
