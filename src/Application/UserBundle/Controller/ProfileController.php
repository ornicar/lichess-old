<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\ProfileController as BaseProfileController;
use FOS\UserBundle\Model\UserInterface;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Application\UserBundle\Document\User;
use Lichess\ChartBundle\Chart\UserEloChart;
use Lichess\ChartBundle\Chart\UserWinChart;

class ProfileController extends BaseProfileController
{
    public function closeAccountAction()
    {
        if ($this->container->get('request')->getMethod() == 'POST') {
            $response = new RedirectResponse($this->container->get('router')->generate('fos_user_user_show', array(
                'username' => $this->getAuthenticatedUser()->getUsername()
            )));
            $this->container->get('lichess_user.account_closer')->closeAccount($response);

            return $response;
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:closeAccount.html.twig');
    }

    public function updateProfileAction()
    {
        $bio = $this->container->get('request')->request->get('bio');
        $this->getAuthenticatedUser()->setBio($bio);
        $this->container->get('doctrine.odm.mongodb.document_manager')->flush();

        $response = new Response(json_encode(array('bio' => $bio)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function profileBioAction()
    {
        return new Response($this->getAuthenticatedUser()->getBio());
    }

    public function autocompleteAction()
    {
        $term = $this->container->get('request')->query->get('term');
        $usernames = $this->container->get('fos_user.repository.user')->findUsernamesBeginningWith($term);

        $response = new Response(json_encode($usernames));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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
        $users->setItemCountPerPage(40);
        $users->setPageRange(3);
        $pagerUrl = $this->container->get('router')->generate('fos_user_user_list');

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:list.html.twig', compact('users', 'pagerUrl'));
    }

    public function viewAction($username)
    {
        $user = $this->container->get('fos_user.repository.user')->findOneByUsernameCanonical($username);
        if (!$user) {
            $response = $this->container->get('templating')->renderResponse('FOSUserBundle:User:unknownUser.html.twig', array('username' => $username));
            $response->setStatusCode(404);

            return $response;
        }
        $authenticatedUser = $this->getAuthenticatedUser();

        $critic = $this->container->get('lichess.critic.user');
        $critic->setUser($user);

        $winChart = new UserWinChart($critic);
        $history = $this->container->get('lichess.repository.history')->findOneByUserOrCreate($user);
        $eloChart = new UserEloChart($history);

        $page = $this->container->get('request')->query->get('page', 1);
        $query = $this->container->get('lichess.repository.game')->createRecentStartedOrFinishedByUserQuery($user);
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($page);
        $games->setItemCountPerPage(6);
        $games->setPageRange(3);
        if ($page > 1 && $page > $games->count()) {
            throw new NotFoundHttpException('No more items');
        }
        $pagerUrl = $this->container->get('router')->generate('fos_user_user_show', array('username' => $user->getUsername()));

        if ($authenticatedUser instanceof User && $user->is($authenticatedUser)) {
            $template = 'FOSUserBundle:User:home.html.twig';
        } else {
            $template = 'FOSUserBundle:User:show.html.twig';
        }

        return $this->container->get('templating')->renderResponse($template, compact('user', 'critic', 'eloChart', 'winChart', 'games', 'pagerUrl'));
    }

    protected function getAuthenticatedUser()
    {
        return $this->container->get('security.context')->getToken()->getUser();
    }
}
