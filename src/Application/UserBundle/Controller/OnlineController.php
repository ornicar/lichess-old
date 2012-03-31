<?php

namespace Application\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends ContainerAware
{
    public function listOnlineAction()
    {
        $users = $this->container->get('fos_user.repository.user')->findOnlineUsersSortByElo();
        $nbPlayers = $this->container->get('lila')->nbPlayers();

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:listOnline.html.twig', compact('users', 'nbPlayers'));
    }
}
