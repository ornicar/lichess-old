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
        $this->latency = $latency * 2;
        $this->delay   = $delay;
    }

    public function poll($userState, $userMessageId, $userEntryId)
    {
        $nbLoops = round($this->latency / $this->delay);

        for ($i=0; $i<$nbLoops; $i++) {
            // Get state from APC
            $state = $this->memory->getState();

            // If the client and server state differ, update the client
            if($userState != $state) break;

            if ($userMessageId !== false) {
                // Get message id from APC
                $messageId = $this->memory->getMessageId();

                // If the client and server message id differ, update the client
                if($userMessageId != $messageId) break;
            }

            if (0 === $i % 5) {
              // Get entry id from APC
              $entryId = $this->memory->getEntryId();

              // If the client and server entry id differ, update the client
              if($userEntryId != $entryId) break;
            }

            usleep($this->delay * 1000 * 1000);
        }

        return $state;
    }
}
