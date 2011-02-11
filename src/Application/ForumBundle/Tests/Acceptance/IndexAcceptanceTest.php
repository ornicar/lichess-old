<?php

namespace Application\ForumBundle\Tests\Acceptance;

class IndexAcceptanceTest extends AbstractAcceptanceTest
{
    public function testIndexShowsListOfCategories()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'forum_index'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(5, $crawler->filter('.categories .subject a')->count());
    }

    public function testIndexCategoryHasTitleAndDescription()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'forum_index'));
        $this->assertEquals('General Chess Discussion', $crawler->filter('.categories .subject a')->first()->text());
        $this->assertEquals('The place to discuss general Chess topics', $crawler->filter('.categories .description')->first()->text());
    }

    public function testIndexCategoriesShowLastPostName()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'forum_index'));
        $this->assertRegexp('/by lichess.org staff/', $crawler->filter('.categories td.last_post')->first()->text());
        $this->assertRegexp('/by user1/', $crawler->filter('.categories td.last_post')->last()->text());
    }
}
