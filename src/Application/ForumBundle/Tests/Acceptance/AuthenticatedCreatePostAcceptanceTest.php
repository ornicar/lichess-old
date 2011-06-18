<?php

namespace Application\ForumBundle\Tests\Acceptance;

class AuthenticatedCreatePostAcceptanceTest extends AbstractCreatePostAcceptanceTest
{
    public function testSeeNoAuthorNameField()
    {
        $client = self::createClient();
        $crawler = $this->requestPostCreationPage($client);
        $this->assertEquals(0, $crawler->filter('#lichess_forum input.authorName')->count());
    }

    public static function createClient(array $options = array(), array $server = array())
    {
        $client = static::createPersistentClient();
        static::authenticate($client);

        return $client;
    }
}
