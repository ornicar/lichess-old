<?php

namespace Application\ForumBundle\Tests\Acceptance;

abstract class AbstractCreateTopicAcceptanceTest extends AbstractAcceptanceTest
{
    public function testClickOnCreateTopicLinkGoesToTheFormPage()
    {
        $client = $this->createClient();
        $crawler = $this->requestCategoryPage($client);
        $crawler = $client->click($crawler->selectLink('Create a new topic')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('New topic', $crawler->filter('#lichess_forum h1')->text());
    }

    public function testTopicCreationPageShowsAForm()
    {
        $client = $this->createClient();
        $crawler = $this->requestTopicCreationPage($client);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('#lichess_forum form')->count());
        $this->assertEquals('Create the topic', $crawler->filter('#lichess_forum .submit')->text());
    }

    public function testAuthenticatedUserSeeNoAuthorNameField()
    {
        $client = $this->createClient();
        $this->authenticate($client, 'user1');
        $crawler = $this->requestTopicCreationPage($client);
        $this->assertEquals(0, $crawler->filter('#lichess_forum input.authorName')->count());
    }

    protected function requestCategoryPage($client)
    {
        $url = $this->generateUrl($client, 'forum_category_show', array('slug' => 'general-chess-discussion'));

        return $client->request('GET', $url);
    }

    protected function requestTopicCreationPage($client)
    {
        $url = $this->generateUrl($client, 'forum_category_topic_new', array('slug' => 'general-chess-discussion'));

        return $client->request('GET', $url);
    }
}
