<?php

namespace Application\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends ContainerAware
{
    public function onlineAction($username)
    {
        $data = array();
        $data['nbp'] = $this->container->get('lichess.memory')->getNbActivePlayers();
        $data['nbm'] = $this->container->get('ornicar_message.messenger')->getUnreadCacheForUsername($username);
        $this->container->get('lichess_user.online.cache')->setUsernameOnline($username);
        $response = new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));

        return $response;
    }

    public function updateOnlineAction()
    {
        $this->container->get('lichess_user.online.updater')->update();

        return new Response('done');
    }

    public function listOnlineAction()
    {
        $users = $this->container->get('fos_user.repository.user')->findOnlineUsersSortByElo();
        $nbPlayers = $this->container->get('lichess.memory')->getNbActivePlayers();

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:listOnline.html.twig', compact('users', 'nbPlayers'));
    }
}
