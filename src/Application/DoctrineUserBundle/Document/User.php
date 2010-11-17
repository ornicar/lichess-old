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
     * ELO score of the player
     *
     * @mongodb:Field(type="float")
     * @var float
     */
    protected $elo = 1200.00;

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
    }

    public function setUsername($username)
    {
        parent::setUsername($username);
        $this->setEmail($username.'@lichess.org');
    }
}
