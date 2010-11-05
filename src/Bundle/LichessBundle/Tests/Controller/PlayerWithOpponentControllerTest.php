<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayerWithOpponentControllerTest extends WebTestCase
{
    protected function createGameWithFriend()
    {
        $p1 = $this->createClient();
        $crawler = $p1->request('GET', '/friend/white');
        $form = $crawler->selectButton('Start')->form();
        $p1->submit($form);
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $selector = 'div.lichess_game_not_started.waiting_opponent div.lichess_overboard input';
        $inviteUrl = $crawler->filter($selector)->attr('value');
        $h1 = preg_replace('#^.+([\w-]{12}+)$#', '$1', $p1->getRequest()->getUri());

        $p2 = $this->createClient();
        $crawler = $p2->request('GET', $inviteUrl);
        $redirectUrl = $crawler->filter('a.join_redirect_url')->attr('href');
        $p2->request('GET', $redirectUrl);
        $crawler = $p2->followRedirect();
        $this->assertTrue($p2->getResponse()->isSuccessful());
        $h2 = preg_replace('#^.+([\w-]{12}+)$#', '$1', $p2->getRequest()->getUri());

        return array($p1, $h1, $p2, $h2);
    }

    public function testClaimDrawWithoutThreefold()
    {
        list($p1, $h1, $p2, $h2) = $this->createGameWithFriend();
        $p1->request('GET', '/claim-draw/'.$h1);
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_current_player p:contains("Your turn")')->count());
    }

    public function testClaimDrawWithThreefold()
    {
        list($p1, $h1, $p2, $h2) = $data = $this->createGameWithFriend();
        $this->applyMoves($data, array(
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
        ));
        $p1->request('GET', '/claim-draw/'.$h1);
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_current_player p:contains("Draw")')->count());
    }

    public function testClaimDrawWithThreefoldBadPlayer()
    {
        list($p1, $h1, $p2, $h2) = $data = $this->createGameWithFriend();
        $this->applyMoves($data, array(
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
        ));
        $p2->request('GET', '/claim-draw/'.$h2);
        $this->assertTrue($p2->getResponse()->isRedirect());
        $crawler = $p2->followRedirect();
        $this->assertTrue($p2->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('div.lichess_current_player p:contains("Draw")')->count());
    }

    protected function applyMoves(array $data, array $moves)
    {
        list($p1, $h1, $p2, $h2) = $data;
        foreach($moves as $it => $move) {
            list($from, $to) = explode(' ', $move);
            $player = $it%2 ? $p2 : $p1;
            $id = $it%2 ? $h2 : $h1;
            $moveUrl = '/move/'.$id.'/0';
            $player->request('POST', $moveUrl, array('from' => $from, 'to' => $to));
        }
    }

    protected function getSyncUrl($id)
    {
        $client = $this->createClient();
        $client->request('GET', $id);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"sync":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }

    protected function getMoveUrl($id)
    {
        $client = $this->createClient();
        $client->request('GET', $id);
        return str_replace(array('\\', '9999999'), array('', '0'), preg_replace('#.+"move":"([^"]+)".+#s', '$1', $client->getResponse()->getContent()));
    }
}
