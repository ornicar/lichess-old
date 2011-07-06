<?php

namespace Lichess\OpeningBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lichess\OpeningBundle\Form\HookFormType;
use Lichess\OpeningBundle\Document\Hook;

class HookController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessOpeningBundle::index.html.twig');
    }

    public function newAction()
    {
        $form = $this->get('lichess.form.manager')->createHookForm();
        if ($this->get('request')->getMethod() === 'POST') {
            $form->bindRequest($this->get('request'));
            if ($form->isValid()) {

            }
        }

        return $this->render('LichessOpeningBundle:Config:hook.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
    }

    public function pollAction()
    {
        $hooks = $this->get('lichess_hook.hook_repository')->findAll();

        return $this->render('LichessOpeningBundle::hooks.html.twig', array('hooks' => $hooks));
    }
}
