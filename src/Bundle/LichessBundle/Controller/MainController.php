<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends Controller
{
    public function statusAction()
    {
        $load = sys_getloadavg();

        return new Response(implode(' ', array(
            $this->container->get('lichess.memory')->getNbActivePlayers(),
            $this->container->get('lichess.repository.game')->getNbGames(),
            $this->container->get('lichess.repository.game')->countPlaying(),
            $this->container->get('lichess.repository.game')->countRecentlyCreated(),
            sprintf('%.01f', $load[0])
        )), 200, array('content-type' => 'text/plain'));
    }

    public function howManyGamesNowAction()
    {
      return new Response(
        $this->container->get('lichess.repository.game')->countPlaying(),
        200,
        array('content-type' => 'text/plain')
      );
    }

    public function todolistAction()
    {
        $text = file_get_contents($this->container->getParameter('kernel.root_dir').'/../TODO');
        $items = explode("\n", preg_replace('/^\*\s/m', '', $text));
        array_pop($items);

        return $this->render('LichessBundle:Main:todolist.html.twig', array('items' => $items));
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
        return new RedirectResponse($this->generateUrl('lichess_wiki_show', array('slug' => 'Lichess-Wiki')));
    }
}
