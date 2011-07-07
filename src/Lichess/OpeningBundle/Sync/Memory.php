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

    public function __construct($timeout)
    {
        $this->timeout = (int) $timeout;
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
