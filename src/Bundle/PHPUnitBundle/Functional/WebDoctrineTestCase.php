<?php

namespace Bundle\PHPUnitBundle\Functional;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventArgs;

abstract class WebDoctrineTestCase extends WebTestCase
{
    protected $em;
    protected $connections = array();
    
    public function postConnect(EventArgs $e)
    {
        $conn = $e->getConnection();
        $this->connections[] = $conn;
        $conn->beginTransaction();
    }

    public function setUp()
    {
        parent::setUp();
        $self =& $this; 
        $this->client->setDebug(function($k) use (&$self){
            // $evm = $this->client->getContainer()->getService('Doctrine.ORM.EntityManager');
            $evm = $k->getContainer()->getService('Doctrine.Dbal.DefaultConnection.EventManager');
            $evm->addEventListener('postConnect', $self);
        });
        $crawler = $this->client->request('GET', '/');
        
        $evm = new EventManager();
        $this->em = $this->client->getContainer()->getService('Doctrine.ORM.EntityManager');
        $test = $this->client->getContainer()->getService('Doctrine.Dbal.DefaultConnection.EventManager');
        $evm->addEventListener('postConnect', $this);
        echo "\n\n\nSETUPING\n";
        // $this->em = $this->kernel->getContainer()->getService('doctrine.orm.entity_manager');
        echo "==========beginTra==========\n";
        // $this->em = $this->kernel->getContainer()->getKernelService()->getContainer()->getService('Doctrine.ORM.DefaultEntityManager');
        // $this->em->beginTransaction();
    }

    public function tearDown()
    {
        echo "==========endTra==========\n";
        while($connection = array_shift($this->connections)) {
            echo "X: rollbacking a connection\n";
            $connection->rollback();
        }
        $this->connections = array();
        // $this->em->rollback();
        parent::tearDown();
    }
    
}
