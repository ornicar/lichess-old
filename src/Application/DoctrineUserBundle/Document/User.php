<?php

namespace Application\DoctrineUserBundle\Document;
use Bundle\DoctrineUserBundle\Document\User as BaseUser;

/**
 * @mongodb:Document(repositoryClass="Bundle\DoctrineUserBundle\Document\UserRepository")
 */
class User extends BaseUser
{
}
