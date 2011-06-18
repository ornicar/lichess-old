<?php

namespace Application\UserBundle\Tests\Acceptance;

class SignupAcceptanceTest extends AbstractAcceptanceTest
{
    protected $username = 'user-signup-test';
    protected $password = 'password-signup-test';

    public function testSignup()
    {
        $client = $this->createPersistentClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_registration_register'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/Sign up/', $crawler->filter('.lichess_title')->text());
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_registration_form[username]'] = $this->username;
        $form['fos_user_registration_form[plainPassword]'] = $this->password;
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_show', array('username' => $this->username)));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/'.$this->username.'/', $crawler->filter('.lichess_title')->text());
    }

    public function testSignupWithBadUsername()
    {
        $client = $this->createPersistentClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_registration_register'));
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_registration_form[username]'] = 'x';
        $form['fos_user_registration_form[plainPassword]'] = $this->password;
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/username is too short/', $client->getResponse()->getContent());
    }

    public function testSignupWithBadPassword()
    {
        $client = $this->createPersistentClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_registration_register'));
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_registration_form[username]'] = $this->username;
        $form['fos_user_registration_form[plainPassword]'] = '';
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/enter a password/', $client->getResponse()->getContent());
    }

    public function testSignupWithExistingUsername()
    {
        $client = $this->createPersistentClient();

        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_registration_register'));
        $form = $crawler->selectButton('Sign up')->form();
        $form['fos_user_registration_form[username]'] = 'user1';
        $form['fos_user_registration_form[plainPassword]'] = $this->password;
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/This username is not available/', $client->getResponse()->getContent());
    }

    public function setUp()
    {
        $manager = self::createClient()->getContainer()->get('fos_user.user_manager');
        $user = $manager->findUserByUsername($this->username);
        if($user) {
            $manager->deleteUser($user);
        }
    }
}
