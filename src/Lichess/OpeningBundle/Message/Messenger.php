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

    public function send(User $user, $text)
    {
        $message = new Message($user->getUsername(), $text, $this->request->getClientIp());

        if ($message->isValid() && !$this->isSpam($message)) {
            $this->repository->add($message);
        }

        return $message;
    }

    public function isSpam(Message $message)
    {
        $recentMessages = iterator_to_array($this->repository->findRecent(10, true));

        foreach ($recentMessages as $recentMessage) {
            if ($message->isLike($recentMessage)) {
                return true;
            }
        }

        $countSameClient = 0;
        foreach (array_slice($recentMessages, 0, 6) as $recentMessage) {
            if ($message->isSameClient($recentMessage)) {
                $countSameClient++;
            }
        }
        if ($countSameClient == 6) {
            return true;
        }

        $countSameClient = 0;
        foreach ($recentMessages as $recentMessage) {
            if ($message->isSameClient($recentMessage)) {
                $countSameClient++;
            }
        }
        if ($countSameClient >= 9) {
            return true;
        }

        return false;
    }
}
