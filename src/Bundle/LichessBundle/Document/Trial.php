<?php

namespace Bundle\LichessBundle\Document;

use FOS\UserBundle\Model\User;

/**
 * Determine if a player is innocent or guilty
 *
 * @mongodb:Document(
 *   collection="trial",
 *   repositoryClass="Bundle\LichessBundle\Document\TrialRepository"
 * )
 */
class Trial
{
    /**
     * Unique ID
     *
     * @var string
     * @mongodb:Id
     */
    protected $id;

    /**
     * Game we are judging
     *
     * @mongodb:ReferenceOne(targetDocument="Bundle\LichessBundle\Document\Game")
     * @var Game
     */
    protected $game;

    /**
     * User we are judging
     *
     * @var User
     * @mongodb:ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $user;

    /**
     * Suspicious level of this user
     *
     * @var int
     * @mongodb:Field(type="int")
     * @mongodb:Index(order="desc")
     */
    protected $score;

    /**
     * Decision
     * null = Undecided
     * true = guilty
     * false= innocent
     *
     * @var bool
     * @mongodb:Field(type="boolean")
     * @mongodb:Index()
     */
    protected $verdict;

    /**
     * Return the suspicious level of culpability
     *
     * @return int
     **/
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set the score
     *
     * @return null
     **/
    public function setScore($score)
    {
        $this->score = round(min(100, max(0, $score)));
    }

    /**
     * @return bool
     */
    public function getVerdict()
    {
        return $this->verdict;
    }

    /**
     * @param  bool
     * @return null
     */
    public function setVerdict($verdict)
    {
        $this->verdict = $verdict;
    }

    /**
     * Tells if the verdict is positive
     *
     * @return bool
     **/
    public function isGuilty()
    {
        return true === $this->verdict;
    }

    /**
     * Tells if the verdict is negative
     *
     * @return bool
     **/
    public function isInnocent()
    {
        return false === $this->verdict;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  User
     * @return null
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param  Game
     * @return null
     */
    public function setGame($game)
    {
        $this->game = $game;
    }
}
