<?php

namespace Application\MessageBundle\Form;

use Application\MessageBundle\Akismet;
use Ornicar\MessageBundle\Form\MessageFormHandler as BaseMessageFormHandler;

class MessageFormHandler extends BaseMessageFormHandler
{
    protected $akismet;

    public function setAkismet(Akismet $akismet)
    {
        $this->akismet = $akismet;
    }

    public function process(Message $message)
    {
        $this->form->setData($message);

        if ('POST' === $this->request->getMethod()) {
            $data = $this->request->get($this->form->getName(), array());
            $user = $this->userManager->findUserByUsername($data['to']);

            $this->form->bind(array_merge(
                $data,
                array('to' => $user)
            ));
            $message = $this->form->getData();

            if ($this->akismet->isMessageSpam($message)) {
                $this->form['body']->addError(new Error('Sorry, but your message looks like spam. If you think it is an error, send me an email.'));
                return false;
            }

            if ($this->form->isValid()) {
                $this->messenger->send($message);

                return true;
            }
        }

        return false;
    }

}
