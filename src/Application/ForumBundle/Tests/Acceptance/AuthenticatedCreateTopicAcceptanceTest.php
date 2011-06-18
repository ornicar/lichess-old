<?php

namespace Application\ForumBundle\Tests\Acceptance;

class AuthenticatedCreateTopicAcceptanceTest extends AbstractCreateTopicAcceptanceTest
{
    public function testSeeNoAuthorNameField()
    {
        $client = self::createClient();
        $crawler = $this->requestTopicCreationPage($client);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('#lichess_forum form')->count());
        $this->assertEquals('Create the topic', $crawler->filter('#lichess_forum .submit')->text());
        $this->assertEquals(0, $crawler->filter('#lichess_forum input.authorName')->count());
    }

    public static function createClient(array $options = array(), array $server = array())
    {
        $client = static::createPersistentClient();
        static::authenticate($client);

        return $client;
    }
}
