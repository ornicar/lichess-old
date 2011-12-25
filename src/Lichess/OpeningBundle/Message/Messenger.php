<?php

namespace Lichess\OpeningBundle\Message;

use Lichess\OpeningBundle\Document\MessageRepository;
use Lichess\OpeningBundle\Sync\Memory;
use Lichess\OpeningBundle\Document\Message;
use Application\UserBundle\Document\User;
use Symfony\Component\HttpFoundation\Request;

class Messenger
{
    public function __construct(MessageRepository $repository, Memory $memory, Request $request)
    {
        $this->repository = $repository;
        $this->memory = $memory;
        $this->request = $request;
    }

    public function send($user, $text)
    {
        $message = new Message($user, $text, $this->request->getClientIp());

        if ($message->isValid() && !$this->isSpam($message)) {
            $this->repository->add($message);
        }

        return $message;
    }

    public function isSpam(Message $message)
    {
        $recentMessages = $this->repository->findRecent(10, true);

        foreach ($recentMessages as $recentMessage) {
            if ($message->isLike($recentMessage)) {
                return true;
            }
        }

        $countSameClient = 0;
        foreach ($recentMessages as $recentMessage) {
            if ($message->isSameClient($recentMessage)) {
                $countSameClient++;
            }
        }

        if ($countSameClient > 7) {
            return true;
        }

        return false;
    }
}
