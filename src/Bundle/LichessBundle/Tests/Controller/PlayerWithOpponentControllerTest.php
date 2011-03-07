<?php

namespace Bundle\LichessBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Closure;

class PlayerWithOpponentControllerTest extends WebTestCase
{
    protected function createGameWithFriend($color = 'white', Closure $configClosure = null)
    {
        $p1 = $this->createClient();
        $crawler = $p1->request('GET', '/friend');
        $form = $crawler->filter('.submit.'.$color)->form();
        if ($configClosure) {
            $configClosure($form);
        }
        $p1->submit($form, array('config[color]' => $color));
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

    public function testAbort()
    {
        list($p1, $h1, $p2, $h2) = $this->createGameWithFriend();
        $p1->request('GET', '/abort/'.$h1);
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('div.lichess_current_player p:contains("Game aborted")')->count());
    }

    public function testAbortAndRematchFullProcess()
    {
        list($p1, $h1, $p2, $h2) = $this->createGameWithFriend();
        $p1->request('GET', '/abort/'.$h1);
        $crawler = $p1->followRedirect();
        $p1->request('POST', $crawler->selectLink('Rematch')->attr('href'));
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $response = json_decode($p1->getResponse()->getContent(), true);
        $this->assertEquals('redirect', $response['e'][0]['type']);

        $crawler = $p1->back();
        $this->assertEquals(0, $crawler->selectLink('Rematch')->count());
        $this->assertRegexp('/Rematch offer sent/', $p1->getResponse()->getContent());

        $crawler = $p2->reload();
        $link = $crawler->selectLink('Join the game');
        $this->assertEquals(1, $link->count());
        $p2->request('POST', $link->attr('href'));
        $this->assertTrue($p2->getResponse()->isSuccessful());
        $response = json_decode($p2->getResponse()->getContent(), true);
        $this->assertEquals('redirect', $response['e'][0]['type']);
        $url = $response['e'][0]['url'];
        $this->assertRegexp('#/\w{12}#', $url);

        $p2->request('GET', $url);
        $this->assertTrue($p2->getResponse()->isSuccessful());
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

    /**
     * @expectedException LogicException
     */
    public function testDrawOfferTooEarly()
    {
        list($p1, $h1, $p2, $h2) = $data = $this->createGameWithFriend();

        $p1->request('GET', '/offer-draw/'.$h1);
    }

    public function testDrawOffer()
    {
        list($p1, $h1, $p2, $h2) = $data = $this->createGameWithFriend();

        $this->applyMoves($data, array('b1 c3', 'b8 c6'));

        $crawler = $p1->request('GET', '/'.$h1);
        $p1->click($crawler->selectLink('Offer draw')->link());
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals('Cancel', $crawler->filter('div.offered_draw a')->text());

        return $data;
    }

    /**
     * @depends testDrawOffer
     */
    public function testDrawCancel(array $data)
    {
        list($p1, $h1, $p2, $h2) = $data;

        $p1->request('GET', '/cancel-draw-offer/'.$h1);
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('div.offered_draw a')->count());

        // Can no more accept draw this turn
        $this->assertEquals(0, $crawler->filter('a.offer_draw')->count());

        // Do some move do be allowed to offer a draw
        $this->applyMoves($data, array('a2 a4', 'a7 a5'));
        $crawler = $p1->request('GET', '/'.$h1);
        $this->assertEquals(1, $crawler->filter('a.offer_draw')->count());

        return $data;
    }

    /**
     * @depends testDrawCancel
     */
    public function testDrawDecline(array $data)
    {
        list($p1, $h1, $p2, $h2) = $data;

        $p1->request('GET', '/offer-draw/'.$h1);
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals('Cancel', $crawler->filter('.offered_draw a')->text());

        $crawler = $p2->request('GET', '/'.$h2);

        // p2 sees the draw offer
        $this->assertRegexp('/Your opponent offers a draw/', $p2->getResponse()->getContent());
        $this->assertEquals(1, $crawler->filter('.offered_draw')->count());
        $this->assertEquals(2, $crawler->filter('.offered_draw a')->count());

        // p2 declines the draw
        $p2->click($crawler->selectLink('Decline')->link());
        $this->assertTrue($p2->getResponse()->isRedirect());
        $crawler = $p2->followRedirect();
        $this->assertTrue($p2->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('.offered_draw')->count());

        // p1 reloads and no more sees his draw offer
        $crawler = $p1->request('GET', '/'.$h1);
        $this->assertEquals(0, $crawler->filter('.offered_draw')->count());

        return $data;
    }

    /**
     * @depends testDrawDecline
     */
    public function testDrawAccept(array $data)
    {
        list($p1, $h1, $p2, $h2) = $data;

        $p2->request('GET', '/offer-draw/'.$h2);
        $this->assertTrue($p2->getResponse()->isRedirect());
        $crawler = $p2->followRedirect();
        $this->assertTrue($p2->getResponse()->isSuccessful());
        $this->assertEquals('Cancel', $crawler->filter('.offered_draw a')->text());

        $crawler = $p1->request('GET', '/'.$h2);

        // p1 sees the draw offer
        $this->assertRegexp('/Your opponent offers a draw/', $p1->getResponse()->getContent());
        $this->assertEquals(1, $crawler->filter('.offered_draw')->count());
        $this->assertEquals(2, $crawler->filter('.offered_draw a')->count());

        // p1 accepts the draw
        $p1->click($crawler->selectLink('Accept')->link());
        $this->assertTrue($p1->getResponse()->isRedirect());
        $crawler = $p1->followRedirect();
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('.offered_draw')->count());

        // p2 reloads and no more sees his draw offer
        $crawler = $p2->request('GET', '/'.$h2);
        $this->assertEquals(0, $crawler->filter('.offered_draw')->count());
    }

    /**
     * @expectedException LogicException
     */
    public function testOutoftimeNoClock()
    {
        list($p1, $h1, $p2, $h2) = $data = $this->createGameWithFriend('white', function($form) {
            $form['config[time]'] = 0;
        });

        $p1->request('POST', '/outoftime/'.$h1.'/1');
    }

    public function testOutOfTimeTooEarly()
    {
        list($p1, $h1, $p2, $h2) = $data = $this->createGameWithFriend('white', function($form) {
            $form['config[time]'] = 10;
        });

        $p1->request('POST', '/outoftime/'.$h1.'/1');
        $this->assertTrue($p1->getResponse()->isSuccessful());

        $crawler = $p1->request('GET', '/'.$h1);
        $this->assertTrue($p1->getResponse()->isSuccessful());
        $this->assertRegexp('/Your turn/', $crawler->filter('.lichess_player')->text());
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
            $this->assertTrue($player->getResponse()->isSuccessful());
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
