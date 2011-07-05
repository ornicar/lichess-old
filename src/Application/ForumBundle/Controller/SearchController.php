<?php

namespace Application\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    public function searchAction()
    {
        $query = $this->get('request')->query->get('q', '');
        if ($query) {
            $results = $this->get('foq_elastica.finder.default.forum')->findPaginated($query);

            return $this->render('LichessForumBundle:Search:results.html.twig', compact('query', 'results'));
        }

        return $this->render('LichessForumBundle:Search:search.html.twig');
    }
}
