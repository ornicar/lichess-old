<?php

namespace Lichess\HookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessHookBundle:Default:index.html.twig');
    }
}
