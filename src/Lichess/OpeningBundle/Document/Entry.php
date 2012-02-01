<?php

namespace Lichess\OpeningBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Application\UserBundle\Document\User;

/**
 * @MongoDB\Document(
 *   collection="lobby_entry",
 *   repositoryClass="Lichess\OpeningBundle\Document\EntryRepository"
 * )
 */
class Entry
{
    /**
     * @MongoDB\Id(strategy="increment")
     */
    protected $id;

    /**
     * @MongoDB\Hash
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
