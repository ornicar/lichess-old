<?php

namespace Application\ForumBundle\Tests\Model;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Application\ForumBundle\Document\Post;

class PostTest extends WebTestCase
{
    public function testIndex()
    {
        $message = "Hey men,\n have a look to http://en.lichess.org/arJ5NOP5-abc or to en.lichess.org/arJ5NOP5-abc";
        $expected = "Hey men,\n have a look to http://en.lichess.org/arJ5NOP5 or to en.lichess.org/arJ5NOP5";
        $post = new Post();
        $post->setMessage($message);
        $this->assertEquals($expected, $post->getMessage());
    }
}
