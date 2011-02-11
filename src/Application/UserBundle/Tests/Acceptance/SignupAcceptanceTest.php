<?php

namespace Application\UserBundle\Tests\Acceptance;

class SignupAcceptanceTest extends AbstractAcceptanceTest
{
    protected $username = 'user-signup-test';
    protected $password = 'password-signup-test';

    public function testSignup()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_create'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/Sign up/', $crawler->filter('.lichess_title')->text());
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_user_form[username]'] = $this->username;
        $form['fos_user_user_form[plainPassword]'] = $this->password;
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        die($client->getResponse()->getContent());
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
        $form['fos_user_user_form[plainPassword]'] = $this->password;
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
        $form['fos_user_user_form[username]'] = $this->username;
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
        $form['fos_user_user_form[username]'] = 'user1';
        $form['fos_user_user_form[plainPassword]'] = $this->password;
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/username was already taken/', $client->getResponse()->getContent());
    }

    public function setUp()
    {
        $manager = $this->createClient()->getContainer()->get('fos_user.user_manager');
        $user = $manager->findUserByUsername($this->username);
        if($user) {
            $manager->deleteUser($user);
        }
    }
}
