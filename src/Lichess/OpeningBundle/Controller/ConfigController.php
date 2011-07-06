<?php

namespace Lichess\OpeningBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfigController extends Controller
{
    public function friendAction()
    {
        $form = $this->get('lichess.form.manager')->createFriendForm();

        if ($this->get('request')->getMethod() === 'POST') {
            $form->bindRequest($this->get('request'));
            if($form->isValid()) {
                $player = $this->get('lichess.starter.friend')->start($form->getData());
                $this->flush();
                return new RedirectResponse($this->generateUrl('lichess_wait_friend', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessOpeningBundle:Config:friend.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
    }

    public function aiAction()
    {
        $form = $this->get('lichess.form.manager')->createAiForm();

        if ($this->get('request')->getMethod() === 'POST') {
            $form->bindRequest($this->get('request'));
            if($form->isValid()) {
                $player = $this->get('lichess.starter.ai')->start($form->getData());
                $this->flush();
                return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessOpeningBundle:Config:ai.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
    }

    protected function flush($safe = true)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => $safe));
    }
}
