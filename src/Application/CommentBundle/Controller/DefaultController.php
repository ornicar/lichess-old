<?php

namespace Application\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CommentBundle:Default:index.html.twig');
    }
}
