<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityController extends  BaseSecurityController
{
    public function loginAction()
    {
        // get the error if any (works with forward and redirect -- see below)
        if ($this->container->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->container->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $this->container->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
            $this->container->get('request')->getSession()->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            $this->container->get('logger')->log($error->getMessage(), 4);
        }

        return new RedirectResponse($this->container->get('request')->server->get('HTTP_REFERER', $this->container->get('router')->generate('lichess_homepage')));
    }
}
