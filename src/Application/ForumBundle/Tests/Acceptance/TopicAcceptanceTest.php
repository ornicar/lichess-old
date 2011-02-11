<?php

namespace Application\ForumBundle\Tests\Acceptance;

class TopicAcceptanceTest extends AbstractAcceptanceTest
{
    public function testTopicPageShowsTitle()
    {
        $client = $this->createClient();
        $crawler = $this->requestTopicPage($client);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('New forum category: Off-Topic Discussion', $crawler->filter('#lichess_forum h1')->text());
    }

    public function testTopicShowsListOfPosts()
    {
        $client = $this->createClient();
        $crawler = $this->requestTopicPage($client);
        $this->assertEquals(2, $crawler->filter('.forum_posts_list .post')->count());
    }

    public function testTopicPostHasAuthor()
    {
        $client = $this->createClient();
        $crawler = $this->requestTopicPage($client);
        $this->assertEquals('lichess.org staff', $crawler->filter('.forum_posts_list .authorName')->first()->text());
        $this->assertRegexp('/^user1/', $crawler->filter('.forum_posts_list .authorName')->last()->text());
    }

    public function testClickOnRegisteredAuthorGoToUserPage()
    {
        $client = $this->createClient();
        $crawler = $this->requestTopicPage($client);
        $crawler = $client->click($crawler->selectLink('user1')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^user1/', $crawler->filter('#lichess_user h1')->text());
    }

    protected function requestTopicPage($client)
    {
        $url = $this->generateUrl($client, 'forum_topic_show', array(
            'categorySlug' => 'off-topic-discussion',
            'slug' => 'new-forum-category-off-topic-discussion'
        ));

        return $client->request('GET', $url);
    }
}
