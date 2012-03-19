<?php

namespace Application\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends ContainerAware
{
    public function updateOnlineAction()
    {
        $this->container->get('lichess_opening.hook_cleaner')->removeDeadHooks();
        $this->container->get('doctrine.odm.mongodb.document_manager')->flush();

        return new Response('ok');
    }

    public function listOnlineAction()
    {
        $users = $this->container->get('fos_user.repository.user')->findOnlineUsersSortByElo();
        $nbPlayers = $this->container->get('lichess.memory')->getNbActivePlayers();

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:listOnline.html.twig', compact('users', 'nbPlayers'));
    }
}
