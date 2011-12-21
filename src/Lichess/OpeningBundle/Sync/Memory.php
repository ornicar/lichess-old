<?php

namespace Lichess\OpeningBundle\Sync;

use Lichess\OpeningBundle\Document\Hook;

class Memory
{
    /**
     * If a hook doesn't synchronize during this amount of seconds,
     * it is removed
     *
     * @var int
     */
    protected $timeout;
    protected $stateKey = 'lichess.hook_state';
    protected $messageIdKey = 'lichess.message_id';

    protected $messageRepository;

    public function __construct($messageRepository, $timeout)
    {
        $this->messageRepository = $messageRepository;
        $this->timeout = (int) $timeout;
    }

    public function incrementState()
    {
        if (!apc_inc($this->stateKey)) {
            apc_store($this->stateKey, 1);
        }
    }

    public function getState()
    {
        $state = apc_fetch($this->stateKey);
        if (!$state) {
            $state = 1;
            apc_store($this->stateKey, 1);
        }

        return $state;
    }

    public function setMessageId($id)
    {
        apc_store($this->messageIdKey, (int) $id);
    }

    public function getMessageId()
    {
        $id = apc_fetch($this->messageIdKey);
        if (!$id) {
            $id = $this->messageRepository->getLastId();
            apc_store($this->messageIdKey, $id);
        }

        return $id;
    }

    public function setAlive(Hook $hook)
    {
        $this->setHookIdAlive($hook->getOwnerId());
    }

    public function setHookIdAlive($hookId)
    {
        apc_store($this->getHookKey($hookId), true, $this->timeout);
    }

    public function isAlive(Hook $hook)
    {
        return (boolean) apc_fetch($this->getHookKey($hook->getOwnerId()));
    }

    public function getHookKey($hookId)
    {
        return 'hook.'.$hookId.'.alive';
    }
}
