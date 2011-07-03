<?php

namespace Lichess\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessSearchBundle:Search:index.html.twig');
    }
}
