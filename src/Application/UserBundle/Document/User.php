<?php

namespace Application\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Document\User as BaseUser;
use Ornicar\MessageBundle\Model\ParticipantInterface;

/**
 * @MongoDB\Document(
 *   repositoryClass="Application\UserBundle\Document\UserRepository",
 *   collection="user"
 * )
 * @MongoDB\Index(keys={"elo"="desc", "enabled"="asc"})
 */
class User extends BaseUser implements ParticipantInterface
{
    /**
     * Default ELO score a user receives on registration
     */
    const STARTING_ELO = 1200;

    /**
     * Id
     *
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id = null;

    /**
     * @Assert\Regex(pattern="/^[\w\-]+$/", message="Invalid username. Please use only letters, numbers and dash", groups={"Registration","FacebookRegistration"}),
     * @Assert\NotBlank(message="Please enter a username", groups="Registration"),
     * @Assert\MinLength(limit=2, message="The username is too short", groups="Registration"),
     * @Assert\MaxLength(limit=30, message="The username is too long (30 chars max)", groups="Registration")
     */
    protected $username;

    /**
     * ELO score of the user
     *
     * @MongoDB\Field(type="int")
     * @MongoDB\Index(order="desc")
     * @var int
     */
    protected $elo = null;

    /**
     * @MongoDB\Field(type="int")
     * @var int
     */
    protected $nbGames = 0;

    /**
     * @MongoDB\Field(type="int")
     * @var int
     */
    protected $nbRatedGames = 0;

    /**
     * Whether the user is online or not
     *
     * @MongoDB\Field(type="boolean")
     * @MongoDB\Index(order="desc")
     * @var bool
     */
    protected $isOnline = false;

    /**
     * Small text description
     *
     * @MongoDB\Field(type="string")
     * @var string
     */
    protected $bio = null;

    /**
     * Preferred game config
     *
     * @var array
     * @MongoDB\Field(type="hash")
     */
    protected $gameConfigs = array();

    /**
     * Index the enabled field
     *
     * @MongoDB\Index
     */
    protected $enabled;

    /**
     * Account creation date
     *
     * @var DateTime
     * @MongoDB\Field(type="date")
     */
    protected $createdAt;

    /**
     * Whether the user is banned from public chat or not
     *
     * @MongoDB\Field(type="boolean")
     * @var bool
     */
    protected $isChatBan;

    /**
     * Whether the user uses an engine
     *
     * @MongoDB\Field(type="boolean")
     * @var bool
     */
    protected $engine = false;

    /**
     * User settings
     *
     * @var array
     * @MongoDB\Field(type="hash")
     */
    protected $settings = array();

    public function __construct()
    {
        parent::__construct();

        $this->createdAt = new \DateTime();
        $this->setElo(self::STARTING_ELO);
    }

    public function getSetting($name, $default)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
    }

    public function setEngine($x)
    {
        $this->engine = (bool) $x;
    }

    public function isEngine()
    {
        return $this->engine;
    }

    public function isChatBan()
    {
        return $this->isChatBan;
    }

    public function canSeeChat()
    {
        return !$this->isChatBan && $this->getNbGames() >= 3;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return array
     */
    public function getGameConfigs()
    {
        return $this->gameConfigs;
    }

    /**
     * @param  array
     * @return null
     */
    public function setGameConfigs($gameConfigs)
    {
        $this->gameConfigs = $gameConfigs;
    }

    public function getGameConfig($key)
    {
        return isset($this->gameConfigs[$key]) ? $this->gameConfigs[$key] : null;
    }

    public function setGameConfig($key, array $config)
    {
        $this->gameConfigs[$key] = $config;
    }

    /**
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @param  string
     * @return null
     */
    public function setBio($bio)
    {
        $this->bio = $bio;
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
    }

    /**
     * @return int
     */
    public function getNbGames()
    {
        return $this->nbGames;
    }

    /**
     * @param  float
     * @return null
     */
    public function setNbGames($nbGames)
    {
        $this->nbGames = $nbGames;
    }

    /**
     * @return int
     */
    public function getNbRatedGames()
    {
        return $this->nbRatedGames;
    }

    /**
     * @param  float
     * @return null
     */
    public function setNbRatedGames($nbRatedGames)
    {
        $this->nbRatedGames = $nbRatedGames;
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
