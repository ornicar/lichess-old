<?php

namespace Bundle\LichessBundle\Boost;

class Handler
{
    /**
     * Handles the client ping requests
     */
    public static function ping()
    {
        if (isset($_GET['hook_id'])) {
            self::getHookSynchronizer()->setHookIdAlive($_GET['hook_id']);
        }
    }

    protected static function getHookSynchronizer()
    {
        require_once __DIR__.'/../../../Lichess/OpeningBundle/Sync/Memory.php';

        return new \Lichess\OpeningBundle\Sync\Memory(self::$softTimeout);
    }
}
