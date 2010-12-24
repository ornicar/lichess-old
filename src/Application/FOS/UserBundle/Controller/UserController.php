<?php

namespace Application\FOS\UserBundle\Controller;
use Bundle\FOS\UserBundle\Controller\UserController as BaseUserController;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;

class UserController extends BaseUserController
{
    /**
     * Show all users
     **/
    public function listAction()
    {
        $query = $this->get('fos_user.repository.user')->createQueryBuilder()
            ->sort('elo', 'desc');
        $users = new Paginator(new DoctrineMongoDBAdapter($query));
        $users->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $users->setItemCountPerPage(20);
        $users->setPageRange(10);
        $pagerUrl = $this->generateUrl('fos_user_user_list');

        return $this->render('UserBundle:User:list.'.$this->getRenderer(), array('users' => $users));
    }

    public function showAction($username)
    {
        $user = $this->findUserByUsername($username);
        $critic = $this->get('lichess.critic.user');
        $critic->setUser($user);

        $query = $this->get('lichess.repository.game')->createRecentStartedOrFinishedByUserQuery($user);
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(3);
        $games->setPageRange(10);
        $pagerUrl = $this->generateUrl('fos_user_user_show', array('username' => $user->getUsername()));

        return $this->render('UserBundle:User:show.'.$this->getRenderer(), array(
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

        return $this->redirect($this->generateUrl('fos_user_user_show', array(
            'username' => $this->get('security.context')->getUser()->getUsername()
        )));
    }
}
