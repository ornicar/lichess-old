<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TimelineController extends Controller
{
    public function indexAction()
    {
        return $this->render('Lichess:Timeline:index.html.twig');
    }

    public function innerAction()
    {
        $entries = $this->get('lichess.repository.timeline_entry')->findLatests(30);

        return $this->render('Lichess:Timeline:inner.html.twig', compact('entries'));
    }
}
