<?php

namespace Bundle\PHPUnitBundle\Functional\Service;

use Bundle\PHPUnitBundle\Functional\WebTestCase;

abstract class Service
{
    protected $test;
    protected $options;
    
    function __construct(WebTestCase $test, array $options = array())
    {
        $this->test = $test;
        $this->options = $options;
        $this->init();
    }
    
    public function init()
    {
        
    }
    
    public function setUp()
    {
    }

    public function tearDown()
    {
    }
}