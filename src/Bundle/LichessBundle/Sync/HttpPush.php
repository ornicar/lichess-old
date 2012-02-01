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
        $nbLoops = max(1, round($this->latency / $this->delay)) - 1;
        $sleepUs = $this->delay * 1000 * 1000;

        usleep($sleepUs);

        for ($i=0; $i<$nbLoops; $i++) {

            if (!$this->isSynced($player, $version)) {
                break;
            }

            usleep($sleepUs);
        }
    }

    public function isSynced(Player $player, $version)
    {
        // Get user cache from APC
        $memoryVersion = $this->memory->getVersion($player);

        return false !== $memoryVersion && $version == $memoryVersion;
    }
}
