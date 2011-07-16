<?php

namespace Lichess\MessageBundle\Twig\Extension;

use Ornicar\MessageBundle\Security\ParticipantProviderInterface;
use Ornicar\MessageBundle\Model\ReadableInterface;
use Ornicar\MessageBundle\Provider\ProviderInterface;
use Ornicar\MessageBundle\Twig\Extension\MessageExtension as BaseMessageExtension;
use Lichess\MessageBundle\Cache;

class MessageExtension extends BaseMessageExtension
{
    protected $cache;

    public function __construct(ParticipantProviderInterface $participantProvider, ProviderInterface $provider, Cache $cache)
    {
        parent::__construct($participantProvider, $provider);

        $this->cache = $cache;
    }

    /**
     * Gets the number of unread messages for the current user
     *
     * @return int
     */
    public function getNbUnread()
    {
        if (null === $this->nbUnreadMessagesCache) {
            $this->nbUnreadMessagesCache = $this->cache->getNbUnread($this->getAuthenticatedParticipant());
        }

        return $this->nbUnreadMessagesCache;
    }

    /**
     * Gets the current authenticated user
     *
     * @return ParticipantInterface
     */
    protected function getAuthenticatedParticipant()
    {
        return $this->participantProvider->getAuthenticatedParticipant();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ornicar_message';
    }
}
