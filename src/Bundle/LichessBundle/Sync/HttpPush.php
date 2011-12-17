<?php

namespace Bundle\LichessBundle\Sync;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Sync\Memory;

class HttpPush
{
    protected $memory;

    protected $latency;
    protected $delay;

    public function __construct(Memory $memory, $latency, $delay)
    {
        $this->memory  = $memory;
        $this->latency = $latency;
        $this->delay   = $delay;
    }

    public function poll(Player $player, $version)
    {
        $nbLoops = min(1, round($this->latency / $this->delay));

        for ($i=0; $i<$nbLoops; $i++) {
            // Get user cache from APC
            $userVersion = $this->memory->getVersion($player);

            // If the user has no cache, hit the application
            if(false === $userVersion) break;

            // If the client and server version differ, update the client
            if($userVersion != $version) break;

            usleep($this->delay * 1000 * 1000);
        }
    }
}
