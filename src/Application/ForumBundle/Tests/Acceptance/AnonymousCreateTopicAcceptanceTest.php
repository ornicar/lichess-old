<?php

namespace Application\ForumBundle\Tests\Acceptance;

class AnonymousCreateTopicAcceptanceTest extends AbstractCreateTopicAcceptanceTest
{
    public function testSeeAuthorNameField()
    {
        $client = $this->createClient();
        $crawler = $this->requestTopicCreationPage($client);
        $this->assertEquals(1, $crawler->filter('#lichess_forum input.authorName')->count());
    }
}
