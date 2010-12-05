<?php

namespace Bundle\LichessBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $dm;
    
    public function setUp()
    {
        $this->kernel = $this->createKernel();
        $this->kernel->boot();
        
        $this->dm = $this->kernel->getContainer()->get('lichess.object_manager');

        if ($this->dm instanceof \Doctrine\ORM\EntityManager) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->dm);
            $queries = $this->dm->getMetadataFactory()->getAllMetadata();

            try {
                $schemaTool->createSchema($queries);
            }
            catch (\Exception $e) {

            }
        }
    }

    protected function inviteFriend($color = 'white')
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/'.$color);
        $crawler = $client->click($crawler->selectLink('Play with a friend')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());

        return array($client, $crawler);
    }

    protected function inviteAnybody($color = 'white', $join = false)
    {
        $client = $this->createClient();
        !$join && $client->getContainer()->get('lichess.repository.seek')->createQueryBuilder('g')->delete()->getQuery()->execute();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Play with anybody')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Start')->form();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        if($join) {
            $this->assertTrue($client->getResponse()->isSuccessful());
            $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
            $client->request('GET', $redirectUrl);
            $this->assertTrue($client->getResponse()->isRedirect());
            $crawler = $client->followRedirect();
        }
        $this->assertTrue($client->getResponse()->isSuccessful());

        return array($client, $crawler);
    }
}
