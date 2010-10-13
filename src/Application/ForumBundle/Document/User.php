<?php

namespace Application\ForumBundle\Document;

/**
 * @mongodb:Document(
 *   collection="user",
 *   repositoryClass="Application\ForumBundle\Document\UserRepository"
 * )
 */
class User
{
    /**
     * @mongodb:Id
     */
    protected $id;
}
