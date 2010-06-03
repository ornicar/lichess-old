<?php

use Bundle\PHPUnitBundle\Functional\WebTestCase as BaseWebTestCase;

use Bundle\PHPUnitBundle\Client;

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
}
