<?php

namespace Application\ForumBundle\Tests\Acceptance;

abstract class AbstractCreatePostAcceptanceTest extends AbstractAcceptanceTest
{
    protected function requestPostCreationPage($client)
    {
        $url = $this->generateUrl($client, 'forum_topic_show', array(
            'categorySlug' => 'off-topic-discussion',
            'slug' => 'new-forum-category-off-topic-discussion'
        ));

        return $client->request('GET', $url);
    }
}
