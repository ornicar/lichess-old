<?php

namespace Application\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use FOS\UserBundle\Model\User;

class LichessUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUser';
    }
}
