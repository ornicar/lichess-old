<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\UserController as BaseUserController;
use FOS\UserBundle\Model\UserInterface;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController extends BaseUserController
{
    public function autocompleteAction()
    {
        $term = $this->container->get('request')->query->get('term');
        $usernames = $this->container->get('fos_user.repository.user')->findUsernamesBeginningWith($term);

        $response = $this->createResponse(json_encode($usernames));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function onlineAction($username)
    {
        $data = array();
        $data['nbp'] = $this->container->get('lichess_synchronizer')->getNbConnectedPlayers();
        $data['nbm'] = $this->container->get('ornicar_message.messenger')->getUnreadCacheForUsername($username);
        $this->container->get('fos_user.onliner')->setUsernameOnline($username);
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function updateOnlineAction()
    {
        $repo = $this->container->get('fos_user.repository.user');
        $onliner = $this->container->get('fos_user.onliner');
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
        $this->container->get('fos_user.object_manager')->flush();

        die('done');
    }

    public function listOnlineAction()
    {
        $users = $this->container->get('fos_user.repository.user')->findOnlineUsersSortByElo();
        $nbPlayers = $this->container->get('lichess_synchronizer')->getNbConnectedPlayers();

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:listOnline.html.twig', compact('users', 'nbPlayers'));
    }

    /**
     * Show all users
     **/
    public function listAction()
    {
        $query = $this->container->get('fos_user.repository.user')->createQueryBuilder()
            ->sort('elo', 'desc');
        $users = new Paginator(new DoctrineMongoDBAdapter($query));
        $users->setCurrentPageNumber($this->container->get('request')->query->get('page', 1));
        $users->setItemCountPerPage(20);
        $users->setPageRange(3);
        $pagerUrl = $this->container->get('router')->generate('fos_user_user_list');

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:list.html.twig', compact('users'));
    }

    public function showAction($username)
    {
        try {
            $user = $this->container->get('fos_user.repository.user')->findOneByUsernameCanonical($username);
        } catch(NotFoundHttpException $e) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:User:unknownUser.html.twig', array('username' => $username));
        }
        $critic = $this->container->get('lichess.critic.user');
        $critic->setUser($user);

        $query = $this->container->get('lichess.repository.game')->createRecentStartedOrFinishedByUserQuery($user);
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->container->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(3);
        $games->setPageRange(10);
        $pagerUrl = $this->container->get('router')->generate('fos_user_user_show', array('username' => $user->getUsername()));

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:show.html.twig', compact('user', 'critic', 'games', 'pagerUrl'));
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if(!$user instanceof UserInterface) {
            throw new NotFoundHttpException('No authenticated user - cannot confirm registration');
        }

        return new RedirectResponse($this->container->get('router')->generate('fos_user_user_show', array('username' => $user->getUsername())));
    }
}
