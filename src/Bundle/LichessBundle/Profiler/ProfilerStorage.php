<?php

namespace Bundle\LichessBundle\Profiler;
use Symfony\Components\HttpKernel\Profiler\ProfilerStorage as Base;

class ProfilerStorage extends Base
{

    protected function read()
    {
    }

    public function write($data)
    {
    }

    protected function initDb($readOnly = true)
    {
    }

    protected function exec($db, $query, array $args = array())
    {
    }

    protected function close($db)
    {
    }

    public function purge()
    {
    }
}
