<?php

namespace Application\ForumBundle\Tests\Acceptance;

class CategoryAcceptanceTest extends AbstractAcceptanceTest
{
    public function testCategoryPageShowsTitleAndDescription()
    {
        $client = self::createClient();
        $crawler = $this->requestCategoryPage($client);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('General Chess Discussion', $crawler->filter('#lichess_forum h1')->text());
        $this->assertEquals('The place to discuss general Chess topics', $crawler->filter('p.description')->text());
    }

    public function testCategoryShowsListOfTopics()
    {
        $client = self::createClient();
        $crawler = $this->requestCategoryPage($client);
        $this->assertGreaterThanOrEqual(1, $crawler->filter('.forum_topics_list .subject a')->count());
    }

    public function testCategoryTopicHasTitle()
    {
        $client = self::createClient();
        $crawler = $this->requestCategoryPage($client);
        $this->assertRegexp('/(new topic subject|New forum category: General Chess Discussion)/', $crawler->filter('.forum_topics_list .subject a')->first()->text());
    }

    public function testCategoryTopicsShowLastPostName()
    {
        $client = self::createClient();
        $crawler = $this->requestCategoryPage($client);
        $this->assertRegexp('/by (user1|lichess.org staff)/', $crawler->filter('.forum_topics_list td.last_post')->first()->text());
    }

    public function testClickOnTopicTitleGoesToTopic()
    {
        $client = self::createClient();
        $crawler = $this->requestCategoryPage($client);
        $crawler = $client->click($crawler->selectLink('New forum category: General Chess Discussion')->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('New forum category: General Chess Discussion', $crawler->filter('#lichess_forum h1')->text());
    }

    protected function requestCategoryPage($client)
    {
        $url = $this->generateUrl($client, 'herzult_forum_category_show', array('slug' => 'general-chess-discussion'));

        return $client->request('GET', $url);
    }
}
