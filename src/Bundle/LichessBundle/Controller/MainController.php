<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessBundle:Main:index.html.twig', array('color' => 'white'));
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
        $nbConnectedPlayers = $this->get('lichess_synchronizer')->getNbConnectedPlayers();
        $response = new Response($nbConnectedPlayers ?: "0");
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

    public function localeAction($locale)
    {
        if($this->get('lichess.translation.manager')->isAvailable($locale)) {
            $this->get('session')->setLocale($locale);
            $this->get('session')->setFlash('locale_change', $locale);
        }
        $baseUrl = $this->generateUrl('lichess_homepage', array(), true);
        $localeUrl = $this->generateUrl('lichess_locale', array('locale' => $locale), true);
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if(empty($referer) || 0 != strpos($referer, $baseUrl) || 0 === strpos($referer, $localeUrl)) {
            $referer = $baseUrl;
        }
        return new RedirectResponse($referer);
    }

    public function localeLinksAction()
    {
        $locales = $this->container->get('lichess.translation.manager')->getAvailableLanguages();
        unset($locales[$this->container->get('session')->getLocale()]);
        ksort($locales);
        return $this->render('LichessBundle:Main:localeLinks.html.twig', array('locales' => $locales));
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
