<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

class MainController extends Controller
{

    public function indexAction($color)
    {
        return $this->render('LichessBundle:Main:index', array('color' => $color));
    }

    public function localeAction($locale)
    {
        $this->container->getSessionService()->setLocale($locale);
        $baseUrl = $this->generateUrl('lichess_homepage', array(), true);
        $localeUrl = $this->generateUrl('lichess_locale', array('locale' => $locale), true);
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if(empty($referer) || 0 != strpos($referer, $baseUrl) || 0 === strpos($referer, $localeUrl)) {
            $referer = $baseUrl;
        }
        return $this->redirect($referer);
    }

    public function aboutAction()
    {
        return $this->render('LichessBundle:Main:about');
    }

    public function notFoundAction()
    {
        if($this->getRequest()->isXmlHttpRequest()) {
            $response = $this->createResponse('You sould not do that.');
        }
        else {
            $response = $this->render('LichessBundle:Main:notFound');
        }
        $response->setStatusCode(404);
        return $response;
    }
}
