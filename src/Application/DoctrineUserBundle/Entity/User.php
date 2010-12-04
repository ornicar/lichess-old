<?php

namespace Application\DoctrineUserBundle\Entity;
use Bundle\DoctrineUserBundle\Entity\User as BaseUser;

/**
 * @orm:Entity(repositoryClass="Application\DoctrineUserBundle\Entity\UserRepository")
 * @orm:Table(name="users",indexes={@orm:Index(name="elo_idx", columns={"elo"})})
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
     * @orm:Column(type="float")
     * @var float
     */
    protected $elo = null;

    /**
     * History of user ELO
     *
     * @orm:Column(type="array")
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
     * Rounded elo
     *
     * @return int
     **/
    public function getRoundElo()
    {
        return round($this->getElo());
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
        return sprintf('%s (%d)', $this->getUsername(), $this->getRoundElo());
    }
}