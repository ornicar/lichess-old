<?php

namespace Application\MessageBundle\Controller;

use Ornicar\MessageBundle\Controller\MessageController as BaseMessageController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Error;

class MessageController extends BaseMessageController
{
    public function createAction()
    {
        $form = $this->get('ornicar_message.form.composition');
        $form->bind($this->get('request'), $this->get('ornicar_message.model.factory')->createComposition());

        if ($form->isValid()) {
            $message = $form->getData()->getMessage();
            $message->setFrom($this->get('security.context')->getToken()->getUser());
            if ($this->get('lichess_message.akismet')->isMessageSpam($message)) {
                $form['body']->addError(new Error('Sorry, but your message looks like spam. If you think it is an error, send me an email.'));
                $this->get('logger')->notice('Message spam block: '.$message->getFrom().' - '.$message->getSubject());
                return $this->render('OrnicarMessageBundle:Message:new.html.twig', compact('form'));
            }
            $this->get('ornicar_message.messenger')->send($message);
            $this->get('ornicar_message.object_manager')->flush();
            $this->get('session')->setFlash('ornicar_message_message_create', 'success');

            return new RedirectResponse($this->generateUrl('ornicar_message_message_sent'));
        }

        return $this->render('OrnicarMessageBundle:Message:new.html.twig', compact('form'));
    }
}
