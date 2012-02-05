<?php

namespace Lichess\ChartBundle\Chart;

use Application\UserBundle\Document\UserRepository;

class UsersEloChart
{
    /**
     * User repository
     *
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * Maximum number of data points
     *
     * @var int
     */
    protected $points = 100;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getColumns()
    {
        return array(
            array('number', 'Elo'),
            array('number', 'Number of players')
        );
    }

    public function getRows()
    {
        $cacheKey = 'lichess_users_elo_chart';
        $data = apc_fetch($cacheKey);
        if (false === $data) {
            $elos = $this->userRepository->getElosArray();
            $distribution = array_count_values($elos);
            unset($distribution[1200]);

            $data = array();
            foreach ($distribution as $elo => $nb) {
                $data[] = array($elo, $nb);
            }
            apc_store($cacheKey, $data, 86400);
        }

        return $data;
    }
}
