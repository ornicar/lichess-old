<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    private function settings()
    {
        return $this->get('lichess_user.settings');
    }

    public function toggleSoundAction()
    {
        $value = $this->settings()->toggle('sound', true);
        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        return new Response($value ? 'on' : 'off');
    }

    public function boardColorAction(Request $request)
    {
        if (!$color = $request->request->get('color', false)) {
            throw new HttpException(400);
        }
        $this->settings()->set('color', $color);
        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        return new Response($color);
    }

    public function aboutAction()
    {
        return new RedirectResponse($this->generateUrl('lichess_wiki_show', array('slug' => 'Lichess-Wiki')));
    }
}
