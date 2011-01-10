<?php

namespace Application\UserBundle\Controller;
use Bundle\FOS\UserBundle\Controller\SecurityController as BaseSecurityController;

class SecurityController extends  BaseSecurityController
{
    public function loginAction()
    {
        return $this->redirect($this->get('request')->server->get('HTTP_REFERER', $this->generateUrl('lichess_homepage')));
    }
}
