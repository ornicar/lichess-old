<?php

namespace Bundle\PHPUnitBundle\Functional;

use Symfony\Foundation\Test\WebTestCase as BaseWebTestCase;
use Symfony\Components\HttpKernel\Test\RequestTester;
use Symfony\Components\HttpKernel\Test\ResponseTester;
use Bundle\PHPUnitBundle\Client;

abstract class WebTestCase extends BaseWebTestCase
{
    protected $client;
    protected $functionalServices;
    /**
     * container 
     * 
     * @var Symfony\Components\DependencyInjection\Container;
     */
    protected $container;

    function __construct() {
    }
    
    protected function buildFunctionalServices()
    {
        
    }
    
    public function hasService($name)
    {
        return isset($this->functionalServices[$name]);
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
        $client->resetSession();
        
        $this->container = $kernel->getContainer();
        
        return $client;
    }

    public function setUp()
    {
        $this->client = $this->createClient();
        
        $this->functionalServices = array();
        $this->buildFunctionalServices();

        foreach($this->functionalServices as $service) {
            $service->setUp();
        }
    }
    
    public function tearDown()
    {
        foreach($this->functionalServices as $service) {
            $service->tearDown();
        }
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
     * getContainer 
     * 
     * @return Symfony\Components\DependencyInjection\Container;
     */
    public function getContainer()
    {
       return $this->container;
    }
    
}
