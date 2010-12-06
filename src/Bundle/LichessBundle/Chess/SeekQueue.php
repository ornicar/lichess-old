<?php

namespace Bundle\LichessBundle\Chess;
use Bundle\LichessBundle\Document\SeekRepository;
use Bundle\LichessBundle\Document\Seek;
use Bundle\LichessBundle\Document\Game;
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

    public function __construct(DocumentManager $objectManager, SeekRepository $repository, Generator $generator, PlayerBlamer $playerBlamer)
    {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->generator = $generator;
        $this->playerBlamer = $playerBlamer;
    }

    public function add(array $variants, array $times, array $modes, $sessionId, $color)
    {
        $seek = new Seek($variants, $times, $modes, $sessionId);

        if($existing = $this->searchMatching($seek)) {
            $game = $existing->getGame();
            $this->generator->applyVariant($game, $seek->getCommonVariant($existing));
            $game->setClockTime($seek->getCommonTime($existing) * 60);
            $game->setIsRated($seek->getCommonMode($existing));
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
