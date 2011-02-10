<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\UserController as BaseUserController;
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
        $nbPlayers = $this->get('lichess_synchronizer')->getNbConnectedPlayers();

        return $this->render('FOSUserBundle:User:listOnline.html.twig', compact('users', 'nbPlayers'));
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
        $users->setPageRange(3);
        $pagerUrl = $this->generateUrl('fos_user_user_list');

        return $this->render('FOSUserBundle:User:list.html.twig', compact('users'));
    }

    public function showAction($username)
    {
        try {
            $user = $this->get('fos_user.repository.user')->findOneByUsernameCanonical($username);
        } catch(NotFoundHttpException $e) {
            return $this->render('FOSUserBundle:User:unknownUser.html.twig', array('username' => $username));
        }
        $critic = $this->get('lichess.critic.user');
        $critic->setUser($user);

        $query = $this->get('lichess.repository.game')->createRecentStartedOrFinishedByUserQuery($user);
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(3);
        $games->setPageRange(10);
        $pagerUrl = $this->generateUrl('fos_user_user_show', array('username' => $user->getUsername()));

        return $this->render('FOSUserBundle:User:show.html.twig', compact('user', 'critic', 'games', 'pagerUrl'));
    }
}
