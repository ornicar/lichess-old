<?php

namespace Application\ForumBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractAcceptanceTest extends WebTestCase
{
    protected function generateUrl(Client $client, $route, $parameters = array())
    {
        return $client->getContainer()->get('router')->generate($route, $parameters);
    }
}
