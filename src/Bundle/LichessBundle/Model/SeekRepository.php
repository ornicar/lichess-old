<?php

namespace Bundle\LichessBundle\Model;

interface SeekRepository {
    
    function findAllSortByCreatedAt();

    function findOneByGame(Game $game);
}