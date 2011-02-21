<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends  BaseSecurityController
{
    public function loginAction()
    {
        // get the error if any (works with forward and redirect -- see below)
        if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
            $this->get('request')->getSession()->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            $this->get('logger')->log($error->getMessage(), 4);
        }

        return $this->redirect($this->get('request')->server->get('HTTP_REFERER', $this->generateUrl('lichess_homepage')));
    }
}
