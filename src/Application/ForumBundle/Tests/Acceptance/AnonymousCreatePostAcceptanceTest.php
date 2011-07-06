<?php

namespace Application\ForumBundle\Tests\Acceptance;

class AnonymousCreatePostAcceptanceTest extends AbstractCreatePostAcceptanceTest
{
    public function testSeeAuthorNameField()
    {
        $client = self::createClient();
        $crawler = $this->requestPostCreationPage($client);
        $this->assertEquals(1, $crawler->filter('#lichess_forum input.authorName')->count());
    }
}
