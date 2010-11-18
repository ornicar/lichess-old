<?php

namespace Application\DoctrineUserBundle\Document;
use Bundle\DoctrineUserBundle\Document\User as BaseUser;

/**
 * @mongodb:Document(
 *   repositoryClass="Bundle\DoctrineUserBundle\Document\UserRepository",
 *   collection="user"
 * )
 */
class User extends BaseUser
{
    /** @validation:Regex(pattern="/^[\w\-]+$/", message="Invalid username. Please use only letters, numbers and dash", groups={"Registration","FacebookRegistration"}) */
    protected $username;

    /**
     * ELO score of the user
     *
     * @mongodb:Field(type="float")
     * @var float
     */
    protected $elo = 1200.00;

    /**
     * History of user ELO
     *
     * @mongodb:Field(type="collection")
     * @var array
     */
    protected $eloHistory = array(1200);

    /**
     * @return array
     */
    public function getEloHistory()
    {
        $h = array(1200);
        $e = $h[0];
        for($i=0;$i<100;$i++) {
            $e = $e+rand(0, 20)-10;
            $h[] = $e;
        }
        return $h;
        return $this->eloHistory;
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
        $this->eloHistory[] = round($elo);
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
