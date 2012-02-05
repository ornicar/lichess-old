<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WikiController extends Controller
{
    public function indexAction()
    {
        return new RedirectResponse($this->generateUrl('lichess_wiki_show', array('slug' => 'Lichess-Wiki')));
    }

    public function showAction($slug)
    {
        $page = $this->get('lichess.repository.wiki_page')->findOneBySlug($slug);

        if (!$page) {
            throw new NotFoundHttpException("No wiki page like " . $slug);
        }

        return $this->render('LichessBundle:Wiki:show.html.twig', compact('page'));
    }

    public function pagesAction()
    {
        $pages = $this->get('lichess.repository.wiki_page')->findAll();

        return $this->render('LichessBundle:Wiki:pages.html.twig', compact('pages'));
    }
}
