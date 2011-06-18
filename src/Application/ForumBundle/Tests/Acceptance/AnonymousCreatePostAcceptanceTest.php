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

    public function testSubmitSpam()
    {
        $client = self::createClient();
        $crawler = $this->requestPostCreationPage($client);
        $form = $crawler->selectButton('Reply')->form();
        $form['forum_post_form[authorName]'] = 'viagra-test-123';
        $form['forum_post_form[message]'] = 'new message';
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertEquals(1, $crawler->filter('#lichess_forum form')->count());
    }
}
