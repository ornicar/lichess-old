<?php

namespace Application\ForumBundle\Entity;
use Bundle\ForumBundle\Entity\Category as BaseCategory;

/**
 * @orm:Entity(repositoryClass="Bundle\ForumBundle\Entity\CategoryRepository")
 * @orm:Table(name="forum_category")
 * @orm:HasLifecycleCallbacks
 */
class Category extends BaseCategory
{
}
