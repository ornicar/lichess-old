<?php

namespace Application\DoctrineUserBundle\Controller;
use Bundle\DoctrineUserBundle\Controller\UserController as BaseUserController;

class UserController extends BaseUserController
{
    public function showAction($username)
    {
        $user = $this->findUserByUsername($username);
        $critic = $this->get('lichess.critic.user');
        $critic->setUser($user);

        return $this->render('DoctrineUserBundle:User:show.'.$this->getRenderer(), array(
            'user'   => $user,
            'critic' => $critic
        ));
    }

    public function confirmedAction()
    {
        return $this->redirect($this->generateUrl('lichess_homepage'));
    }
}
