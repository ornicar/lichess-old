<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TimelineController extends Controller
{
    public function indexAction()
    {
        $entries = $this->get('lichess.repository.timeline_entry')->findLatests(30);

        return $this->render('LichessBundle:Timeline:index.html.twig', compact('entries'));
    }
}
