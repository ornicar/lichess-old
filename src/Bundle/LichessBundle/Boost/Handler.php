<?php

namespace Bundle\LichessBundle\Boost;

class Handler
{
    // param: lichess.memory.soft_timeout
    protected static $softTimeout = 6;

    /**
     * Handles the client ping requests
     */
    public static function ping()
    {
        $synchronizer = self::getSynchronizer();
        $data = array('nbp' => $synchronizer->getNbActivePlayers());

        if (isset($_GET['player_key'])) {
            $synchronizer->setPlayerKeyAlive($_GET['player_key']);
        }
        if (isset($_GET['watcher'])) {
            $synchronizer->registerWatcher($_GET['watcher']);
        }
        if (isset($_GET['get_nb_watchers'])) {
            $data['nbw'] = $synchronizer->getNbWatchers($_GET['get_nb_watchers']);
        }
        if (isset($_GET['hook_id'])) {
            self::getHookSynchronizer()->setHookIdAlive($_GET['hook_id']);
        }
        if (isset($_GET['username'])) {
            self::getUserSynchronizer()->setUsernameOnline($_GET['username']);
            $data['nbm'] = (int) apc_fetch('nbm.'.$_GET['username']);
        }

        return json_encode($data);
    }

    public static function howManyPlayersNow()
    {
        return self::getSynchronizer()->getNbActivePlayers();
    }

    protected static function getSynchronizer()
    {
        require_once __DIR__.'/../Sync/Memory.php';

        return new \Bundle\LichessBundle\Sync\Memory(self::$softTimeout, 100);
    }

    protected static function getHookSynchronizer()
    {
        require_once __DIR__.'/../../../Lichess/OpeningBundle/Sync/Memory.php';

        return new \Lichess\OpeningBundle\Sync\Memory(self::$softTimeout);
    }

    protected static function getUserSynchronizer()
    {
        require_once __DIR__.'/../../../Application/UserBundle/Online/Cache.php';

        return new \Application\UserBundle\Online\Cache(self::$softTimeout);
    }
}
