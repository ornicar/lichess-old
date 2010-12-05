<?php

namespace Bundle\LichessBundle\Model;

use Bundle\DoctrineUserBundle\Model\User;

interface GameRepository {

    function findRecentByUser(User $user);
    
    function findOneById($id);

    function existsById($id);

    function findRecentStartedGameIds($nb);

    function findGamesByIds($ids);

    function getNbGames();

    function getNbMates();

    function createRecentQuery();

    function createRecentByUserQuery(User $user);

    function createByUserQuery(User $user);

    function createRecentStartedOrFinishedByUserQuery(User $user);

    function createRecentStartedOrFinishedQuery();

    function createRecentMateQuery();

    function findSimilar(Game $game, \DateTime $since);
}