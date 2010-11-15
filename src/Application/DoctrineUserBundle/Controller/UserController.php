<?php

namespace Application\DoctrineUserBundle\Controller;
use Bundle\DoctrineUserBundle\Controller\UserController as BaseUserController;

class UserController extends BaseUserController
{
    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        return $this->redirect($this->generateUrl('lichess_homepage'));
    }
}
