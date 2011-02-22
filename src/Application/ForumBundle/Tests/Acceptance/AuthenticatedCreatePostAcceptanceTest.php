<?php

namespace Application\ForumBundle\Tests\Acceptance;

class AuthenticatedCreatePostAcceptanceTest extends AbstractCreatePostAcceptanceTest
{
    public function testSeeNoAuthorNameField()
    {
        $client = $this->createClient();
        $crawler = $this->requestPostCreationPage($client);
        $this->assertEquals(0, $crawler->filter('#lichess_forum input.authorName')->count());
    }

    public function createClient(array $options = array(), array $server = array())
    {
        $client = $this->createPersistentClient();
        $this->authenticate($client);

        return $client;
    }
}
