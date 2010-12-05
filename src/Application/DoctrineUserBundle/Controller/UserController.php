<?php

namespace Application\DoctrineUserBundle\Controller;
use Bundle\DoctrineUserBundle\Controller\UserController as BaseUserController;
use Zend\Paginator\Paginator;

class UserController extends BaseUserController
{
    /**
     * Show all users
     **/
    public function listAction()
    {
        $query = $this->get('doctrine_user.repository.user')->createRecentQuery();

        $adapter = $this->container->getParameter('lichess.paginator.adapter.class');

        $users = new Paginator(new $adapter($query));
        $users->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $users->setItemCountPerPage(20);
        $users->setPageRange(10);
        $pagerUrl = $this->generateUrl('doctrine_user_user_list');

        return $this->render('DoctrineUserBundle:User:list.'.$this->getRenderer(), array('users' => $users));
    }

    public function showAction($username)
    {
        $user = $this->findUserByUsername($username);
        $critic = $this->get('lichess.critic.user');
        $critic->setUser($user);

        $query = $this->get('lichess.repository.game')->createRecentStartedOrFinishedByUserQuery($user);
        $adapter = $this->container->getParameter('lichess.paginator.adapter.class');
        $games = new Paginator(new $adapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(3);
        $games->setPageRange(10);
        $pagerUrl = $this->generateUrl('doctrine_user_user_show', array('username' => $user->getUsername()));

        return $this->render('DoctrineUserBundle:User:show.'.$this->getRenderer(), array(
            'user'     => $user,
            'critic'   => $critic,
            'games'    => $games,
            'pagerUrl' => $pagerUrl
        ));
    }

    public function confirmedAction()
    {
        if(!$this->get('lichess.security.helper')->isAuthenticated()) {
            $this->get('logger')->warn(sprintf('User:confirmed no user authenticated'));
            return $this->redirect($this->generateUrl('lichess_homepage'));
        }

        return $this->redirect($this->generateUrl('doctrine_user_user_show', array(
            'username' => $this->get('security.context')->getUser()->getUsername()
        )));
    }
}
