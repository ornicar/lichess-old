<?php

namespace Application\UserBundle;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\CookieClearingLogoutHandler;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;

/**
 * Closes user accounts
 */
class AccountCloser
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager = null;

    /**
     * @var SecurityContext
     */
    protected $securityContext = null;

    protected $request = null;

    /**
     * @param UserManagerInterface userManager
     * @param SecurityContext securityContext
     */
    public function __construct(UserManagerInterface $userManager, SecurityContext $securityContext, Request $request)
    {
        $this->userManager     = $userManager;
        $this->securityContext = $securityContext;
        $this->request         = $request;
    }

    public function closeAccount(Response $response)
    {
        $user = $this->securityContext->getToken()->getUser();
        $user->setEnabled(false);
        $this->userManager->updateUser($user);

        $cookieHandler = new CookieClearingLogoutHandler($this->request->cookies->all());
        $cookieHandler->logout($this->request, $response, $this->securityContext->getToken());

        $sessionHandler = new SessionLogoutHandler();
        $sessionHandler->logout($this->request, $response, $this->securityContext->getToken());

        $this->securityContext->setToken(null);
    }
}
