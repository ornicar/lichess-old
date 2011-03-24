<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessBundle:Main:index.html.twig');
    }

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
        $enableSound = !$session->get($attributeName);
        $session->set($attributeName, $enableSound);

        return new Response($enableSound ? 'on' : 'off');
    }
    public function aboutAction()
    {
        return $this->render('LichessBundle:Main:about.html.twig');
    }

    public function exceptionAction($exception)
    {
        $code = $exception->getCode();
        if(404 == $code) {
            if($this->get('request')->isXmlHttpRequest()) {
                $response = new Response('You should not do that.');
            }
            else {
                $response = $this->render('LichessBundle:Main:notFound.html.twig');
            }
            $response->setStatusCode(404);
        } else {
            if ($code < 100 || $code > 599) {
                $code = 500;
            }
            if($this->get('request')->isXmlHttpRequest()) {
                $response = new Response('Something went terribly wrong.');
            }
            else {
                $url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
                $response = $this->render('LichessBundle:Main:error.html.twig', array('code' => $code, 'url' => $url));
            }
            $response->setStatusCode($code);
        }

        return $response;
    }
}
