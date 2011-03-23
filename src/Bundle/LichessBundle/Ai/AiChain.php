<?php

namespace Bundle\LichessBundle\Ai;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Logger;
use Exception;
use RunTimeException;

class AiChain implements AiInterface
{
    protected $implementations;
    protected $logger;

    public function __construct(array $implementations, Logger $logger)
    {
        $this->implementations = $implementations;
        $this->logger          = $logger;
    }

    public function move(Game $game, $level)
    {
        foreach ($this->implementations as $implementation) {
            try {
                return $implementation->move($game, $level);
            } catch(Exception $e) {
                $this->logger->err($game, sprintf('Ai:move Crafty %s %s', get_class($e), $e->getMessage()));
            }
        }

        throw new RuntimeException('Failed to move using all available AI implementations');
    }
}
