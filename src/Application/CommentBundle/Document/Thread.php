<?php

namespace Application\CommentBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use FOS\CommentBundle\Document\Thread as BaseThread;

/**
 * @MongoDB\Document(
 *   collection="fos_comment_thread"
 * )
 */
class Thread extends BaseThread
{

}
