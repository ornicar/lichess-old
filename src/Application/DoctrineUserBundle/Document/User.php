<?php

namespace Application\DoctrineUserBundle\Document;
use Bundle\DoctrineUserBundle\Document\User as BaseUser;

/**
 * @mongodb:Document(
 *   repositoryClass="Application\DoctrineUserBundle\Document\UserRepository",
 *   collection="user"
 * )
 */
class User extends BaseUser
{
    /**
     * Default ELO score a user receives on registration
     */
    const STARTING_ELO = 1200;

    /** @validation:Regex(pattern="/^[\w\-]+$/", message="Invalid username. Please use only letters, numbers and dash", groups={"Registration","FacebookRegistration"}) */
    protected $username;

    /**
     * ELO score of the user
     *
     * @mongodb:Field(type="float")
     * @mongodb:Index(order="desc")
     * @var float
     */
    protected $elo = null;

    /**
     * History of user ELO
     *
     * @mongodb:Field(type="hash")
     * @var array
     */
    protected $eloHistory = array();

    public function __construct()
    {
        parent::__construct();

        $this->setElo(self::STARTING_ELO);
    }

    /**
     * @return array
     */
    public function getEloHistory()
    {
        return $this->eloHistory;
    }

    public function setEloHistory(array $eloHistory)
    {
        $this->eloHistory = $eloHistory;
    }

    /**
     * @return float
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * @param  float
     * @return null
     */
    public function setElo($elo)
    {
        $this->elo = round($elo, 2);
        $this->eloHistory[time()] = round($elo);
    }

    public function getMaxElo()
    {
        $eloHistory = $this->getEloHistory();

        return max($eloHistory);
    }

    public function getMaxEloDate()
    {
        $eloHistory = $this->getEloHistory();
        $times = array_keys($eloHistory, max($eloHistory));
        $time =  max($times);

        return date_create()->setTimestamp($time);
    }

    public function setUsername($username)
    {
        parent::setUsername($username);
        $this->setEmail($username.'@lichess.org');
    }

    public function getUsernameWithElo()
    {
        return sprintf('%s (%d)', $this->getUsername(), round($this->getElo()));
    }
}
