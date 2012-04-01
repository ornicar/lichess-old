<?php

namespace Lichess\MessageBundle;

use Ornicar\MessageBundle\Model\MessageInterface;
use Ornicar\MessageBundle\Model\ParticipantInterface;
use Ornicar\MessageBundle\ModelManager\MessageManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class Cache
{
    /**
     * The message manager
     *
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * The user manager
     *
     * @var UserManagerInterface
     */
    protected $userManager;

    public function __construct(MessageManagerInterface $messageManager, UserManagerInterface $userManager)
    {
        $this->messageManager = $messageManager;
        $this->userManager = $userManager;
    }

    public function updateNbUnread(ParticipantInterface $participant)
    {
        $nbUnread = $this->messageManager->getNbUnreadMessageByParticipant($participant);
        apc_store('nbm.'.$participant->getUsernameCanonical(), $nbUnread);

        return $nbUnread;
    }

    public function getNbUnread(ParticipantInterface $participant)
    {
        $nb = apc_fetch('nbm.'.$participant->getUsernameCanonical());
        if (false === $nb) {
            $nb = $this->updateNbUnread($participant);
        }

        return $nb;
    }

    public function getNbUnreadByUsername($username)
    {
        $nb = apc_fetch('nbm.'.$username);
        if (false === $nb) {
            $user = $this->userManager->findUserByUsername($username);
            $nb = $this->updateNbUnread($participant);
        }

        return $nb;
    }
}
