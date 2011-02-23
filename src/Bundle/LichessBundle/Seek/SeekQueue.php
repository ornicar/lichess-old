<?php

namespace Bundle\LichessBundle\Seek;
use Bundle\LichessBundle\Document\SeekRepository;
use Bundle\LichessBundle\Document\Seek;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Doctrine\ODM\MongoDB\DocumentManager;

class SeekQueue
{
    /**
     * Object Manager
     *
     * @var DocumentManager
     */
    protected $objectManager = null;

    /**
     * Seek Repository
     *
     * @var SeekRepository
     */
    protected $repository = null;

    /**
     * Seek matcher
     *
     * @var SeekMatcher
     */
    protected $matcher = null;

    /**
     * Game generator
     *
     * @var Generator
     */
    protected $generator = null;

    /**
     * Player blamer
     *
     * @var PlayerBlamer
     */
    protected $playerBlamer = null;

    const QUEUED = 1;
    const FOUND = 2;

    public function __construct(DocumentManager $objectManager, SeekRepository $repository, SeekMatcher $matcher, Generator $generator, PlayerBlamer $playerBlamer)
    {
        $this->objectManager = $objectManager;
        $this->repository    = $repository;
        $this->matcher       = $matcher;
        $this->generator     = $generator;
        $this->playerBlamer  = $playerBlamer;
    }

    public function add(array $variants, array $times, array $increments, array $modes, $sessionId, $color)
    {
        $seek = new Seek($variants, $times, $increments, $modes, $sessionId);

        if($existing = $this->searchMatching($seek)) {
            $game = $existing->getGame();
            $this->generator->applyVariant($game, $this->matcher->getCommonVariant($seek, $existing));
            $game->setClockTime($this->matcher->getCommonTime($seek, $existing) * 60, $this->matcher->getCommonIncrement($seek, $existing));
            $game->setIsRated($this->matcher->getCommonMode($seek, $existing));
            $this->objectManager->remove($existing);
            $this->playerBlamer->blame($game->getInvited());
            $status = static::FOUND;
        }
        else {
            $game = $this->generator->createGameForPlayer($color)->getGame();
            $seek->setGame($game);
            $this->objectManager->persist($game);
            $this->objectManager->persist($seek);
            $this->playerBlamer->blame($game->getCreator());
            $status = static::QUEUED;
        }

        $this->objectManager->flush();
        return array('status' => $status, 'game' => $game);
    }

    public function remove(Game $game)
    {
        if($seek = $this->repository->findOneByGame($game)) {
            $this->objectManager->remove($seek);
            $this->objectManager->remove($game);
        }
    }

    protected function searchMatching(Seek $seek)
    {
        $seeks = $this->repository->findAllSortByCreatedAt();
        foreach($seeks as $candidate) {
            if($this->matcher->match($seek, $candidate)) {
                return $candidate;
            }
        }
    }

    /**
     * @return DocumentManager
     */
    public function getObjectManager()
    {
      return $this->objectManager;
    }

    /**
     * @param  DocumentManager
     * @return null
     */
    public function setObjectManager($objectManager)
    {
      $this->objectManager = $objectManager;
    }

    /**
     * @return Generator
     */
    public function getGenerator()
    {
      return $this->generator;
    }

    /**
     * @param  Generator
     * @return null
     */
    public function setGenerator($generator)
    {
      $this->generator = $generator;
    }

    /**
     * @return SeekRepository
     */
    public function getRepository()
    {
      return $this->repository;
    }

    /**
     * @param  SeekRepository
     * @return null
     */
    public function setRepository($repository)
    {
      $this->repository = $repository;
    }
}
