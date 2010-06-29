<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class MainController extends Controller
{

    public function indexAction($color)
    {
        return $this->render('LichessBundle:Main:index', array(
            'color' => $color
        ));
    }

    public function aboutAction()
    {
        return $this->render('LichessBundle:Main:about');
    }

    public function notFoundAction()
    {
        $response = $this->render('LichessBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
