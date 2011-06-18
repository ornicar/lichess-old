<?php

namespace Application\ForumBundle\Tests\Acceptance;

class AnonymousCreateTopicAcceptanceTest extends AbstractCreateTopicAcceptanceTest
{
    public function testSeeAuthorNameField()
    {
        $client = self::createClient();
        $crawler = $this->requestTopicCreationPage($client);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('#lichess_forum input.authorName')->count());
    }
}
