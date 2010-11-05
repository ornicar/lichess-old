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
}
