<?php

namespace Application\UserBundle\Document;
use Bundle\FOS\UserBundle\Document\User as BaseUser;

/**
 * @mongodb:Document(
 *   repositoryClass="Application\UserBundle\Document\UserRepository",
 *   collection="user"
 * )
 */
class User extends BaseUser
{
    /**
     * Default ELO score a user receives on registration
     */
    const STARTING_ELO = 1200;

    /**
     * Id
     *
     * @var \MongoId
     * @mongodb:Id
     */
    protected $id = null;

    /**
     * @validation:Validation({
     *      @validation:Regex(pattern="/^[\w\-]+$/", message="Invalid username. Please use only letters, numbers and dash", groups={"Registration","FacebookRegistration"}),
     *      @validation:NotBlank(message="Please enter a username", groups="Registration"),
     *      @validation:MinLength(limit=2, message="The username is too short", groups="Registration"),
     *      @validation:MaxLength(limit=30, message="The username is too long (30 chars max)", groups="Registration")
     * })
     */
    protected $username;

    /**
     * ELO score of the user
     *
     * @mongodb:Field(type="float")
     * @mongodb:Index(order="desc")
     * @var int
     */
    protected $elo = null;

    /**
     * History of user ELO
     *
     * @mongodb:Field(type="hash")
     * @var array
     */
    protected $eloHistory = array();

    /**
     * Whether the user is online or not
     *
     * @mongodb:Field(type="boolean")
     * @mongodb:Index(order="desc")
     * @var bool
     */
    protected $isOnline = false;

    public function __construct()
    {
        parent::__construct();

        $this->setElo(self::STARTING_ELO);
    }

    /**
     * @return bool
     */
    public function getIsOnline()
    {
      return $this->isOnline;
    }

    /**
     * @param  bool
     * @return null
     */
    public function setIsOnline($isOnline)
    {
      $this->isOnline = $isOnline;
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
     * @return int
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
        $this->elo = round($elo);
        $this->eloHistory[time()] = $this->elo;
    }

    public function addElo($elo)
    {
        $this->setElo($this->getElo() + $elo);
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
        return sprintf('%s (%d)', $this->getUsername(), $this->getElo());
    }

    public function is(User $user = null)
    {
        return $user && $user->getUsernameCanonical() === $this->getUsernameCanonical();
    }
}
