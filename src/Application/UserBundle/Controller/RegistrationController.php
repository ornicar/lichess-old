<?php

namespace Application\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RegistrationController extends BaseRegistrationController
{
    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if(!$user instanceof UserInterface) {
            throw new NotFoundHttpException('No authenticated user - cannot confirm registration');
        }

        return new RedirectResponse($this->container->get('router')->generate('fos_user_user_show', array('username' => $user->getUsername())));
    }
}
