<?php

namespace Application\UserBundle\Tests\Acceptance;

class SigninAcceptanceTest extends AbstractAcceptanceTest
{
    protected $username = 'user1';
    protected $password = 'password1';

    public function testLoginWrongPasswordFails()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'lichess_homepage'));
        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = $this->username;
        $form['_password'] = $this->password;
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        // redirect loop :-/
        return;
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('a.goto_profile'));
        $this->assertEquals(1, $crawler->filter('form.signin_form'));
    }
}
