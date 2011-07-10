<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\ProfileController as BaseProfileController;
use FOS\UserBundle\Model\UserInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Application\UserBundle\Document\User;
use Lichess\ChartBundle\Chart\UserEloChart;
use Lichess\ChartBundle\Chart\UserWinChart;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;

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
        $query = $this->container->get('fos_user.repository.user')->createSortedByEloQuery();
        $users = new Pagerfanta(new DoctrineODMMongoDBAdapter($query));
        $users->setCurrentPage($this->container->get('request')->query->get('page', 1))->setMaxPerPage(40);

        return $this->container->get('templating')->renderResponse('FOSUserBundle:User:list.html.twig', compact('users'));
    }

    public function viewAction($username, $withMe = false)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByUsername($username);
        if (!$user) {
            $response = $this->container->get('templating')->renderResponse('FOSUserBundle:User:unknownUser.html.twig', array('username' => $username));
            $response->setStatusCode(404);

            return $response;
        }
        if (!$user->isEnabled()) {
            return $this->container->get('templating')->renderResponse('LichessUserBundle:User:disabled.html.twig', array('user' => $user));
        }
        $authenticatedUser = $this->getAuthenticatedUser();
        $withMe = $withMe && $authenticatedUser;

        $critic = $this->container->get('lichess.critic.user');
        $critic->setUser($user);

        $winChart = new UserWinChart($critic);
        $history = $this->container->get('lichess.repository.history')->findOneByUserOrCreate($user);
        $eloChart = new UserEloChart($history);

        $gameRepository = $this->container->get('lichess.repository.game');
        if ($withMe) {
            $query = $gameRepository->createByUsersQuery($user, $authenticatedUser);
        } else {
            $query = $gameRepository->createRecentStartedOrFinishedByUserQuery($user);
        }
        try {
            $page = $this->container->get('request')->query->get('page', 1);
            $games = new Pagerfanta(new DoctrineODMMongoDBAdapter($query));
            $games->setCurrentPage($page)->setMaxPerPage(10);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException(sprintf('Invalid user game page requested %s (page %s)', $user->getUsername(), $page));
        }

        if ($withMe) {
            $template = 'FOSUserBundle:User:showWithMe.html.twig';
        } elseif ($user && $user->is($authenticatedUser)) {
            $template = 'FOSUserBundle:User:home.html.twig';
        } else {
            $template = 'FOSUserBundle:User:show.html.twig';
        }

        return $this->container->get('templating')->renderResponse($template, compact('user', 'critic', 'eloChart', 'winChart', 'games', 'withMe'));
    }

    protected function getAuthenticatedUser()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $user instanceof User ? $user : null;
    }
}
