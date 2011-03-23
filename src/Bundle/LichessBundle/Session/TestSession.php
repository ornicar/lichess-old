<?php

namespace Bundle\LichessBundle\Session;

use Symfony\Component\HttpFoundation\Session;

class TestSession extends Session
{
    public function hasSession()
    {
        return isset($this->session);
    }

    public function __destruct()
    {
        // do nothing
    }
}
