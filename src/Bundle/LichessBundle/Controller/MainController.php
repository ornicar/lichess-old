<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends Controller
{
    public function todolistAction()
    {
        $text = file_get_contents($this->container->getParameter('kernel.root_dir').'/../TODO');
        $items = explode("\n", preg_replace('/^\*\s/m', '', $text));
        array_pop($items);

        return $this->render('LichessBundle:Main:todolist.html.twig', array('items' => $items));
    }

    public function howManyPlayersNowAction()
    {
        $nbActivePlayers = $this->get('lichess.memory')->getNbActivePlayers();
        $response = new Response($nbActivePlayers ?: "0");
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }

    public function toggleSoundAction()
    {
        $session = $this->get('session');
        $attributeName = 'lichess.sound.enabled';
        $enableSound = !$session->get($attributeName, true);
        $session->set($attributeName, $enableSound);

        return new Response($enableSound ? 'on' : 'off');
    }

    public function aboutAction()
    {
        return $this->render('LichessBundle:Main:about.html.twig');
    }
}
