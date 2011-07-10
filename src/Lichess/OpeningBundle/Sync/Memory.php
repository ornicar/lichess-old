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

    public function __construct($timeout)
    {
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

    public function setAlive(Hook $hook)
    {
        apc_store($this->getAliveKey($hook), time(), $this->timeout);
    }

    public function isAlive(Hook $hook)
    {
        return (boolean) apc_fetch($this->getAliveKey($hook));
    }

    protected function getAliveKey(Hook $hook)
    {
        return 'hook.'.$hook->getId().'.alive';
    }
}
