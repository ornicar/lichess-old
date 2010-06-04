<?php

namespace Bundle\LichessBundle\Profiler;

use Symfony\Framework\ProfilerBundle\DataCollector\DataCollectorManager as Base;
use Symfony\Components\EventDispatcher\Event;
use Symfony\Components\HttpKernel\Response;
use Symfony\Components\HttpKernel\HttpKernelInterface;

class DataCollectorManager extends Base
{
    public function handle(Event $event, Response $response)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getParameter('request_type')) {
            return $response;
        }

        $this->response = $response;

        foreach ($this->collectors as $name => $collector) {
            $collector->getData();
        }

        return $response;
    }
}
