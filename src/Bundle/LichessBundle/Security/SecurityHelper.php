<?php

namespace Bundle\LichessBundle\Security;
use Symfony\Component\Security\SecurityContext;
use Symfony\Component\Security\User\AccountInterface;

class SecurityHelper
{
    protected $context;

    public function __construct(SecurityContext $context)
    {
        $this->context = $context;
    }

    public function isAuthenticated()
    {
        $user = $this->context->getUser();

        return $user instanceof AccountInterface && $user->hasRole('IS_AUTHENTICATED_FULLY');
    }
}
