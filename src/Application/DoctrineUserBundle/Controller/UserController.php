<?php

namespace Application\DoctrineUserBundle\Controller;
use Bundle\DoctrineUserBundle\Controller\UserController as BaseUserController;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;

class UserController extends BaseUserController
{
    /**
     * Show all users
     **/
    public function listAction()
    {
        $query = $this->get('doctrine_user.repository.user')->createQuery()
            ->sort('elo', 'desc');
        $users = new Paginator(new DoctrineMongoDBAdapter($query));
        $users->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $users->setItemCountPerPage(10);
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
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(1);
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
        return $this->redirect($this->generateUrl('doctrine_user_user_show', array(
            'username' => $this->get('security.context')->getUser()->getUsername()
        )));
    }
}
