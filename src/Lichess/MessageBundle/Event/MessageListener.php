<?php

namespace Lichess\MessageBundle\Event;

use Lichess\MessageBundle\Cache;
use Ornicar\MessageBundle\Model\ThreadInterface;
use Ornicar\MessageBundle\Model\MessageInterface;
use Ornicar\MessageBundle\Event\ReadableEvent;
use Ornicar\MessageBundle\Event\MessageEvent;
use Ornicar\MessageBundle\Event\ThreadEvent;

class MessageListener
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function onPostSend(MessageEvent $event)
    {
        $this->updateThreadCache($event->getThread());
    }

    public function onPostRead(ReadableEvent $event)
    {
        $thread = $event->getReadable();
        if ($thread instanceof MessageInterface) {
            $thread = $thread->getThread();
        }
        $this->updateThreadCache($thread);
    }

    public function onPostDelete(ThreadEvent $event)
    {
        $this->updateThreadCache($event->getThread());
    }

    protected function updateThreadCache(ThreadInterface $thread)
    {
        foreach ($thread->getParticipants() as $participant) {
            $this->cache->updateNbUnread($participant);
        }
    }
}
