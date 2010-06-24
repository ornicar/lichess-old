<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class PgnController extends Controller
{
    public function testAction()
    {
        return $this->render('LichessBundle:Pgn:test');
    }
}
