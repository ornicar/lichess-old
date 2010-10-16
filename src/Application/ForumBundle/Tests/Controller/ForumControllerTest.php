<?php

namespace Application\ForumBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ForumControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'forum_index'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('Test category number 1', $crawler->filter('ul.forum_categories_list li a')->first()->text());
    }

    public function generateUrl($client, $route, $parameters = array())
    {
        return $client->getContainer()->get('router')->generate($route, $parameters);
    }
}
