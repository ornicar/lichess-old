<?php

namespace Lichess\MessageBundle\FormFactory;

use Ornicar\MessageBundle\FormFactory\NewThreadMessageFormFactory as BaseNewThreadMessageFormFactory;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Add recipient based on request query parameter
 */
class NewThreadMessageFormFactory extends BaseNewThreadMessageFormFactory
{
    /**
     * The request
     *
     * @var Request
     */
    protected $request;

    /**
     * The user manager
     *
     * @var UserManagerInterface
     */
    protected $userManager;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    protected function createModelInstance()
    {
        $message = parent::createModelInstance();

        if ($to = $this->request->query->get('to')) {
            if ($recipient = $this->userManager->findUserByUsername($to)) {
                $message->setRecipient($recipient);
            }
        }

        return $message;
    }
}
