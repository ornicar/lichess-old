<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Bundle\LichessBundle\Boost\Handler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Should not be hit unless you disabled the boost router
 */
class BoostController extends ContainerAware
{
    public function howManyPlayersNowAction()
    {
        return new Response((string) Handler::howManyPlayersNowAction(), 200, array('content-type' => 'text/plain'));
    }

    public function pingAction()
    {
        return new Response((string) Handler::ping(), 200, array('content-type' => 'application/json'));
    }
}
