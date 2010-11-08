<?php

namespace Bundle\LichessBundle\Chess;
use Bundle\LichessBundle\Document\SeekRepository;
use Bundle\LichessBundle\Document\Seek;
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
     * Game generator
     *
     * @var Generator
     */
    protected $generator = null;

    const QUEUED = 1;
    const FOUND = 2;

    public function __construct(DocumentManager $objectManager, SeekRepository $repository, Generator $generator)
    {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->generator = $generator;
    }

    public function add(array $variants, array $times, $sessionId, $color)
    {
        $seek = new Seek($variants, $times, $sessionId);

        if($existing = $this->searchMatching($seek)) {
            $game = $existing->getGame();
            $this->generator->applyVariant($game, $seek->getCommonVariant($existing));
            $game->setClockTime($seek->getCommonTime($existing) * 60);
            $this->objectManager->remove($existing);
            $response = array('status' => static::FOUND, 'game' => $existing->getGame());
        }
        else {
            $game = $this->generator->createGameForPlayer($color)->getGame();
            $seek->setGame($game);
            $this->objectManager->persist($game);
            $this->objectManager->persist($seek);
            $response = array('status' => static::QUEUED, 'game' => $game);
        }

        $this->objectManager->flush();
        return $response;
    }

    protected function searchMatching(Seek $seek)
    {
        $seeks = $this->repository->findAllSortByCreatedAt();
        foreach($seeks as $candidate) {
            if($candidate->match($seek)) {
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
