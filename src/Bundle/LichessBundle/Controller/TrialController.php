<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Trial;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TrialController extends Controller
{
    public function verdictAction($id, $verdict)
    {
        $trial = $this->container->get('lichess.repository.trial')->find($id);
        $this->container->get('lichess.cheat.judge')->setVerdict($trial, (bool) $verdict);
        $this->container->get('lichess.object_manager')->flush();

        return new RedirectResponse($this->container->get('router')->generate('lichess_trial_list_unresolved'));
    }

    public function listUnresolvedAction()
    {
        return $this->render('LichessBundle:Trial:listUnresolved.html.twig', $this->addNumbers(array(
            'trials'   => $this->createPaginatorForQuery($this->get('lichess.repository.trial')->createUnresolvedQuery()),
            'pagerUrl' => $this->generateUrl('lichess_trial_list_unresolved')
        )));
    }

    public function listGuiltyAction()
    {
        return $this->render('LichessBundle:Trial:listGuilty.html.twig', $this->addNumbers(array(
            'trials'   => $this->createPaginatorForQuery($this->get('lichess.repository.trial')->createGuiltyQuery()),
            'pagerUrl' => $this->generateUrl('lichess_trial_list_guilty')
        )));
    }

    public function listInnocentAction()
    {
        return $this->render('LichessBundle:Trial:listInnocent.html.twig', $this->addNumbers(array(
            'trials'   => $this->createPaginatorForQuery($this->get('lichess.repository.trial')->createInnocentQuery()),
            'pagerUrl' => $this->generateUrl('lichess_trial_list_innocent')
        )));
    }

    protected function addNumbers(array $data)
    {
        return array_merge(array(
            'nbUnresolved' => $this->get('lichess.repository.trial')->getNbUnresolved(),
            'nbGuilty'     => $this->get('lichess.repository.trial')->getNbGuilty(),
            'nbInnocent'   => $this->get('lichess.repository.trial')->getNbInnocent()
        ), $data);
    }

    protected function createPaginatorForQuery($query)
    {
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        return $games;
    }

    protected function flush($safe = true)
    {
        return $this->get('lichess.object_manager')->flush(array('safe' => $safe));
    }
}
