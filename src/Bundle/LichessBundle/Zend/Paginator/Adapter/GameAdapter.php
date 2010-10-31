<?php

namespace Bundle\LichessBundle\Zend\Paginator\Adapter;
use Zend\Paginator\Adapter;
use Bundle\LichessBundle\Persistence\MongoDBPersistence;
use Bundle\LichessBundle\Entities\Game;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Bundle\LichessBundle\Entities\Game objects
 */
class GameAdapter implements Adapter
{
    protected $persistence = null;

    const STARTED = 1;
    const MATE = 2;

    /**
     * @see PaginatorAdapterInterface::__construct
     */
    public function __construct(MongoDBPersistence $persistence, $mode = self::STARTED)
    {
        $this->persistence = $persistence;

        $this->mode = $mode;
    }

    /**
     * @see Zend\Paginator\Adapater::getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $cursor = $this->persistence->getCollection()
            ->find($this->getQuery())
            ->sort(array('upd' => -1))
            ->skip($offset)
            ->limit($itemCountPerPage);

        $games = array();
        foreach($cursor as $data) {
            $games[] = $this->persistence->decode($data['bin']);
        }

        return $games;
    }

    /**
     * @see Zend\Paginator\Adapter::count
     */
    public function count()
    {
        switch($this->mode) {
            case self::STARTED:
                return $this->persistence->getNbGames();
            case self::MATE:
                return $this->persistence->getNbMates();
        }

        throw new \InvalidArgumentException(sprintf('Mode "%s" is not valid', $this->mode));
    }

    protected function getQuery()
    {
        switch($this->mode) {
            case self::STARTED:
                return array('status' => array('$ne' => Game::CREATED));
            case self::MATE:
                return array('status' => GAME::MATE);
        }

        throw new \InvalidArgumentException(sprintf('Mode "%s" is not valid', $this->mode));
    }
}
