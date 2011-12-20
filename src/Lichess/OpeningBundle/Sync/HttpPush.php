<?php

namespace Lichess\OpeningBundle\Sync;

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

    public function poll($userState, $userMessageId)
    {
        $nbLoops = round($this->latency / $this->delay);

        for ($i=0; $i<$nbLoops; $i++) {
            // Get state from APC
            $state = $this->memory->getState();

            // If the client and server state differ, update the client
            if($userState != $state) break;

            // Get message id from APC
            $messageId = $this->memory->getMessageId();

            // If the client and server message id differ, update the client
            if($userMessageId != $messageId) break;

            usleep($this->delay * 1000 * 1000);
        }

        return $state;
    }
}
