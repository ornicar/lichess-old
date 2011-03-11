<?php

namespace Bundle\LichessBundle\Document;

use Application\UserBundle\Document\User;
use MongoId;

/**
 * Represents a user ELO history
 *
 * @mongodb:Document(
 *   collection="user_history",
 *   repositoryClass="Bundle\LichessBundle\Document\HistoryRepository"
 * )
 */
class History
{
    const TYPE_START = 1;
    const TYPE_GAME = 2;
    const TYPE_ADJUST = 3;

    /**
     * Username of the user this history belongs to
     * It is also the history unique mongo ID
     *
     * @var string
     * @mongodb:Id(strategy="none")
     */
    protected $id;

    /**
     * History entries
     *
     * @var array(ts =>
     *     t (Type)
     *     e (ELO)
     *     g (Game ID)
     * )
     * @mongodb:Field(type="hash")
     */
    protected $entries = array();

    protected $cachedEloByTs;

    public function __construct(User $user)
    {
        $this->id = $user->getUsernameCanonical();
        $this->addEntry($user->getCreatedAt()->getTimestamp(), User::STARTING_ELO, self::TYPE_START);
    }

    protected function addEntry($ts, $elo, $type, $gameId = null)
    {
        if (!self::isValidType($type)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid history type', $type));
        }
        $entry = array(
            't' => $type,
            'e' => round($elo),
        );
        if ($gameId) {
            $entry['g'] = $gameId;
        }
        $this->entries[$ts] = $entry;
    }

    public function hasEntry($ts)
    {
        return isset($this->entries[$ts]);
    }

    public function addGame($ts, $elo, $gameId)
    {
        return $this->addEntry($ts, $elo, self::TYPE_GAME, $gameId);
    }

    public function addUnknownGame($ts, $elo)
    {
        return $this->addEntry($ts, $elo, self::TYPE_GAME);
    }

    public function addAdjust($ts, $elo)
    {
        return $this->addEntry($ts, $elo, self::TYPE_ADJUST);
    }

    public function sortEntries()
    {
        ksort($this->entries);
    }

    public function getEloByTs()
    {
        if (null === $this->cachedEloByTs) {
            $this->cachedEloByTs = array();
            foreach ($this->entries as $ts => $entry) {
                $this->cachedEloByTs[$ts] = $entry['e'];
            }
        }

        return $this->cachedEloByTs;
    }

    public function getMaxElo()
    {
        $eloByTs = $this->getEloByTs();

        return max($eloByTs);
    }

    public function getMaxEloDate()
    {
        $eloByTs = $this->getEloByTs();
        $times   = array_keys($eloByTs, max($eloByTs));
        $time    = max($times);

        return date_create()->setTimestamp($time);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public static function isValidType($type)
    {
        return in_array($type, array(self::TYPE_START, self::TYPE_GAME, self::TYPE_ADJUST));
    }
}
