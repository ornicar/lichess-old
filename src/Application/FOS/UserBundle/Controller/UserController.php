<?php

namespace Application\FOS\UserBundle\Controller;
use Bundle\FOS\UserBundle\Controller\UserController as BaseUserController;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends BaseUserController
{
    public function autocompleteAction()
    {
        $term = $this->get('request')->query->get('term');
        $usernames = $this->get('fos_user.repository.user')->findUsernamesBeginningWith($term);

        $response = $this->createResponse(json_encode($usernames));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function onlineAction($username)
    {
        $data = array();
        $data['nbp'] = $this->get('lichess_synchronizer')->getNbConnectedPlayers();
        $data['nbm'] = $this->get('ornicar_message.messenger')->getUnreadCacheForUsername($username);
        $this->get('fos_user.onliner')->setUsernameOnline($username);
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function updateOnlineAction()
    {
        $repo = $this->get('fos_user.repository.user');
        $onliner = $this->get('fos_user.onliner');
        $onlineUsernames = $onliner->getOnlineUsernames();
        $repoUsernames = array();
        foreach($repo->findOnlineUsers() as $user) {
            if(in_array($user->getUsername(), $onlineUsernames)) {
                $repoUsernames[] = $user->getUsername();
            } else {
                $user->setIsOnline(false);
            }
        }
        foreach($onlineUsernames as $username) {
            if(!in_array($username, $repoUsernames)) {
                $user = $repo->findOneByUsername($username);
                $user->setIsOnline(true);
            }
        }
        $this->get('fos_user.object_manager')->flush();

        die('done');
    }

    public function listOnlineAction()
    {
        $users = $this->get('fos_user.repository.user')->findOnlineUsersSortByElo();

        return $this->render('UserBundle:User:listOnline.'.$this->getRenderer(), array(
            'users' => $users
        ));
    }

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
        $users->setPageRange(5);
        $pagerUrl = $this->generateUrl('fos_user_user_list');

        return $this->render('UserBundle:User:list.'.$this->getRenderer(), array(
            'users' => $users
        ));
    }

    public function showAction($username)
    {
        try {
            $user = $this->findUserByUsername($username);
        } catch(NotFoundHttpException $e) {
            return $this->render('UserBundle:User:unknownUser.twig', array('username' => $username));
        }
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
        if(!$this->get('fos_user.templating.helper.security')->isAuthenticated()) {
            $this->get('logger')->warn(sprintf('User:confirmed no user authenticated'));
            return $this->redirect($this->generateUrl('lichess_homepage'));
        }

        return $this->redirect($this->generateUrl('fos_user_user_show', array(
            'username' => $this->get('security.context')->getUser()->getUsername()
        )));
    }
}
