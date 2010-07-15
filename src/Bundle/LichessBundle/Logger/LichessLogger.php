<?php

namespace Bundle\LichessBundle\Logger;

use Symfony\Components\HttpKernel\LoggerInterface;

class LichessLogger implements LoggerInterface
{
    public function log($message, $priority)
    {
    }

    public function emerg($message)
    {
        $this->sysLog('EMERG', $message);
    }

    public function alert($message)
    {
        $this->sysLog('ALERT', $message);
    }

    public function crit($message)
    {
        $this->sysLog('CRITICAL', $message);
    }

    public function err($message)
    {
        $this->sysLog('ERROR', $message);
    }

    public function warn($message)
    {
        $this->sysLog('WARNING', $message);
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

    protected function sysLog($priority, $message)
    {
        error_log(sprintf('Symfony %s %s %s [%s]', $priority, $message, isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '?', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
    }
}
