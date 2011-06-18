<?php

namespace Application\CommentBundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentAcceptanceTest extends WebTestCase
{
    public function testViewComments()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->getUrlForGameWithComments($client));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertGreaterThanOrEqual(3, $crawler->filter('.fos_comment_comment_show')->count());
    }

    public function testAddComment()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->getUrlForGameWithComments($client));

        $form = $crawler->selectButton('Post')->form();
        $form['fos_comment_comment[authorName]'] = $author = uniqid();
        $form['fos_comment_comment[body]'] = $text = uniqid();
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals($author, $crawler->filter('.fos_comment_thread_show .authorName')->first()->text());
        $this->assertEquals($text, $crawler->filter('.fos_comment_thread_show .fos_comment_comment_body')->first()->text());
    }

    protected function getUrlForGameWithComments($client)
    {
        $gameId = $client->getContainer()->get('lichess.repository.game')->findOneBy(array())->getId();

        return '/'.$gameId;
    }
}
