<?php

namespace Bundle\LichessBundle\Logger;

use Symfony\Components\HttpKernel\LoggerInterface;

class BlackHole implements LoggerInterface
{
    public function log($message, $priority)
    {
    }

    public function emerg($message)
    {
        error_log(sprintf('Symfony EMERG %s %s [%s]', $message, $_SERVER['REQUEST_URI'], isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
    }

    public function alert($message)
    {
        error_log(sprintf('Symfony ALERT %s %s [%s]', $message, $_SERVER['REQUEST_URI'], isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
    }

    public function crit($message)
    {
        error_log(sprintf('Symfony CRITICAL %s %s [%s]', $message, $_SERVER['REQUEST_URI'], isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
    }

    public function err($message)
    {
        error_log(sprintf('Symfony ERROR %s %s [%s]', $message, $_SERVER['REQUEST_URI'], isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
    }

    public function warn($message)
    {
        error_log(sprintf('Symfony WARNING %s %s [%s]', $message, $_SERVER['REQUEST_URI'], isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
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
