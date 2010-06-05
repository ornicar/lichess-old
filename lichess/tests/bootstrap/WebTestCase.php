<?php

use Symfony\Foundation\Test\WebTestCase as BaseWebTestCase;

use Symfony\Components\HttpKernel\Test\RequestTester;
use Symfony\Components\HttpKernel\Test\ResponseTester;
use Symfony\Foundation\Test\Client;

/**
 * Extend the genereric TestCase with projet-specific objects (Kernel…)
 *
 */
class WebTestCase extends BaseWebTestCase
{
    protected $kernel;
    
    /**
     * Creates a Kernel.
     *
     * @return Symfony\Foundation\Kernel A Kernel instance
     */
    protected function createKernel()
    {
        return new \LichessKernel('test', true);
    }
    
    /**
     * Add a request tester to the current client associated to its request
     *
     * @return $this
     */
    protected function addRequestTester()
    {
        $this->client->addTester('request', new RequestTester($this->client->getRequest()));
        return $this;
    }
    
    /**
     * Add a response tester to the current client associated to its response
     *
     * @return $this
     */
    protected function addResponseTester()
    {
        $this->client->addTester('response', new ResponseTester($this->client->getResponse()));
        return $this;
    }
    
    /**
     * Creates a Client.
     *
     * @return Bundle\PHPUnitBundle\Client A Client instance
     */
    public function createClient(array $server = array())
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $client = $kernel->getContainer()->getTest_ClientService();
        $client->setServerParameters($server);
        $client->setTestCase($this);
        
        return $client;
    }

    public function setUp()
    {
        $this->client = $this->createClient();
    }
    
}
