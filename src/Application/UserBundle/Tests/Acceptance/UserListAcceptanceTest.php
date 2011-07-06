<?php

namespace Application\UserBundle\Tests\Acceptance;

class UserListAcceptanceTest extends AbstractAcceptanceTest
{
    public function testUserList()
    {
        list($client, $crawler) = $this->requestUserList();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^Chess players/', $crawler->filter('.lichess_title')->first()->text());
        $this->assertEquals('Who is online', $crawler->filter('.lichess_title')->last()->text());
        $this->assertEquals('1', $crawler->filter('.pager span.current')->text());
    }

    protected function requestUserList()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'fos_user_user_list'));

        return array($client, $crawler);
    }
}
