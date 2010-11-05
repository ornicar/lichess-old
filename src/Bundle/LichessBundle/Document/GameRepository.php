<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;

class GameRepository extends DocumentRepository
{
    /**
     * Find one game by its Id
     *
     * @return Game or null
     **/
    public function findOneById($id)
    {
        return $this->find($id);
    }

    /**
     * Find ids of more recent games
     *
     * @return array
     **/
    public function findRecentStartedGameIds($nb)
    {
        $data = $this->createRecentQuery()
            ->field('status')->equals(Game::STARTED)
            ->select('id')
            ->execute();

        return array_map(function($d) { return $d['id']; }, $data);
    }

    /**
     * Find games for the given ids, in the ids order
     *
     * @return array
     **/
    public function findGamesById($ids)
    {
        if(is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $mongoIds = array_map(function($id) { return new \MongoId($id); }, $ids);

        $games = $this->createQuery()
            ->field('_id')->in($mongoIds)
            ->execute();

        // sort games in the order of ids
        $idPos = array_flip($ids);
        usort($games, function($a, $b) use ($idPos)
        {
            return $idPos[$a->getId()] > $idPos[$b->getId()];
        });

        return $games;
    }

    /**
     * Return the number of games
     *
     * @return int
     **/
    public function getNbGames()
    {
        return $this->createQuery()->count();
    }

    /**
     * Return the number of mates
     *
     * @return int
     **/
    public function getNbMates()
    {
        return $this->createQuery()
            ->field('status')->equals(Game::MATE)
            ->count();
    }

    /**
     * Query of all games ordered by updatedAt
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentQuery()
    {
        return $this->createQuery()
            ->sort('updatedAt', 'DESC');
    }

    /**
     * Query of at least started games
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentStartedOrFinishedQuery()
    {
        return $this->createRecentQuery()
            ->field('status', '>', Game::STARTED);
    }

    /**
     * Query of at least mate games
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentMateQuery()
    {
        return $this->createRecentQuery()
            ->field('status', Game::MATE);
    }
}
