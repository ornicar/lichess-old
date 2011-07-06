<?php

namespace Lichess\HookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lichess\HookBundle\Form\HookFormType;
use Lichess\HookBundle\Document\Hook;

class HookController extends Controller
{
    public function indexAction()
    {
        return $this->render('LichessHookBundle::index.html.twig');
    }

    public function newAction()
    {
        $form = $this->get('lichess.form.manager')->createHookForm();
        if ($this->get('request')->getMethod() === 'POST') {
            $form->bindRequest($this->get('request'));
            if ($form->isValid()) {

            }
        }

        return $this->render('LichessHookBundle::new.html.twig', array('form' => $form->createView()));
    }

    public function pollAction()
    {
        $hooks = $this->get('lichess_hook.hook_repository')->findAll();

        return $this->render('LichessHookBundle::hooks.html.twig', array('hooks' => $hooks));
    }
}
