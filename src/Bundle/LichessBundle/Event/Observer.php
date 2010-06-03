<?php

namespace Bundle\LichessBundle\Event;

use Symfony\Foundation\EventDispatcher;
use Symfony\Components\EventDispatcher\Event;

class Observer
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function register()
    {
    }
}
