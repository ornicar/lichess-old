<?php

namespace Application\ForumBundle\Tests\Acceptance;

abstract class AbstractCreatePostAcceptanceTest extends AbstractAcceptanceTest
{
    public function testPostCreationPageShowsAForm()
    {
        $client = $this->createClient();
        $crawler = $this->requestPostCreationPage($client);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('#lichess_forum form')->count());
        $this->assertEquals('Reply', $crawler->filter('#lichess_forum .submit')->attr('value'));
    }

    public function testSubmitEmptyForm()
    {
        $client = $this->createClient();
        $crawler = $this->requestPostCreationPage($client);
        $form = $crawler->selectButton('Reply')->form();
        $client->submit($form);
        $this->assertFalse($client->getResponse()->isRedirect());
        $this->assertEquals(1, $crawler->filter('#lichess_forum form')->count());
    }

    public function testSubmitValidForm()
    {
        $message = 'new reply message '.uniqid();
        $client = $this->createPersistentClient();
        $crawler = $this->requestPostCreationPage($client);
        $form = $crawler->selectButton('Reply')->form();
        $form['forum_post_form[message]'] = $message;
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/'.$message.'/', $client->getResponse()->getContent());

        $client->getContainer()->get('doctrine.odm.mongodb.document_manager')->clear();
        $post = $client->getContainer()->get('forum.repository.post')->findOneByMessage($message);
        $client->getContainer()->get('forum.remover.post')->remove($post);
        $client->getContainer()->get('doctrine.odm.mongodb.document_manager')->flush();
    }

    protected function requestPostCreationPage($client)
    {
        $url = $this->generateUrl($client, 'forum_topic_show', array(
            'categorySlug' => 'off-topic-discussion',
            'slug' => 'new-forum-category-off-topic-discussion'
        ));

        return $client->request('GET', $url);
    }
}
