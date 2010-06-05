<?php

namespace Bundle\LichessBundle\Logger;

use Symfony\Foundation\LoggerInterface;

class BlackHole implements LoggerInterface
{
    public function log($message, $priority)
    {
    }

    public function emerg($message)
    {
    }

    public function alert($message)
    {
    }

    public function crit($message)
    {
    }

    public function err($message)
    {
    }

    public function warn($message)
    {
    }

    public function notice($message)
    {
    }

    public function info($message)
    {
    }

    public function debug($message)
    {
    }
}
