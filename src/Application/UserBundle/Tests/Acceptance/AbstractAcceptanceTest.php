<?php

namespace Application\UserBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;

abstract class AbstractAcceptanceTest extends WebTestCase
{
    protected function createPersistentClient()
    {
        $client = $this->createClient();
        $client->getCookieJar()->set(new Cookie(session_name(), 'test'));

        return $client;
    }

    protected function generateUrl(Client $client, $route, $parameters = array())
    {
        return $client->getContainer()->get('router')->generate($route, $parameters);
    }

    protected function authenticate(Client $client, $username = 'user1', $password = 'password1')
    {
        $client->request('POST', '/login-check', array(
            '_username' => $username,
            '_password' => $password
        ));
    }
}
