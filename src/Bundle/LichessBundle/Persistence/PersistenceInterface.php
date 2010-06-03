<?php

namespace Bundle\LichessBundle\Persistence;

interface PersistenceInterface
{
    public function save($game);

    public function find($hash);

    /**
     * Returns the modification date of the game with this hash 
     * 
     * @param string $hash 
     * @return int timestamp
     */
    public function getUpdatedAt($hash);
}
