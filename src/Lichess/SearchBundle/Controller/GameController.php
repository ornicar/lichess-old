<?php

namespace Lichess\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GameController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessSearch:Game:index.html.twig');
    }
}
