<?php

namespace Application\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityController extends BaseSecurityController
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
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }

        $url = $this->container->get('request')->headers->get('Referer');
        if (empty($url)) {
            $url = $this->container->get('router')->generate('lichess_homepage');
        }

        return new RedirectResponse($url);
    }
}
