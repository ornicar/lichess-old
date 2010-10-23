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

    /**
     * @see PaginatorAdapterInterface::__construct
     */
    public function __construct(MongoDBPersistence $persistence)
    {
        $this->persistence = $persistence;
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
        return $this->persistence->getNbGames();
    }

    protected function getQuery()
    {
        return array('status' => array('$ne' => Game::CREATED));
    }
}
