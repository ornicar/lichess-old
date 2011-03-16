<?php

namespace Application\ForumBundle\Creator;

use Bundle\ForumBundle\Creator\PostCreator as BasePostCreator;
use Bundle\ForumBundle\Model\Post;
use Application\ForumBundle\Timeline\Pusher;
use Doctrine\ODM\MongoDB\DocumentManager;

class PostCreator extends BasePostCreator
{
    protected $timelinePusher;
    protected $objectManager;

    public function __construct(Pusher $timelinePusher, DocumentManager $objectManager)
    {
        $this->timelinePusher = $timelinePusher;
        $this->objectManager = $objectManager;
    }

    public function create(Post $post)
    {
        parent::create($post);

        var_dump($post->getTopic());die;
        /**
         * Required to get topic slug :(
         */
        $this->objectManager->flush();

        $this->timelinePusher->pushPost($post);
    }
}
