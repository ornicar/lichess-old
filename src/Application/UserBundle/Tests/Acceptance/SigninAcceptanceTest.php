<?php

namespace Application\UserBundle\Tests\Acceptance;

class SigninAcceptanceTest extends AbstractAcceptanceTest
{
    protected $username = 'user1';
    protected $password = 'password1';

    public function testLoginValidPasswordSucceeds()
    {
        $client = $this->createPersistentClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'lichess_homepage'));
        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = $this->username;
        $form['_password'] = $this->password;
        $client->submit($form);

        $crawler = $client->request('GET', $this->generateUrl($client, 'lichess_homepage'));
        $this->assertEquals(1, $crawler->filter('a.goto_profile')->count());
        $this->assertEquals(0, $crawler->filter('form.signin_form')->count());
    }
}
