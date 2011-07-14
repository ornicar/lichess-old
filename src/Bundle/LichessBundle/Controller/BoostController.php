<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Bundle\LichessBundle\Boost\Handler;

/**
 * Should not be hit unless you disabled the boost router
 */
class BoostController extends ContainerAware
{
    public function howManyPlayersNowAction()
    {
        return new Response((string) Handler::howManyPlayersNowAction(), 'text/plain');
    }

    public function pingAction()
    {
        return new Response((string) Handler::ping(), 'application/json');
    }
}
