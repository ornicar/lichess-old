<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RuntimeException;
use Lichess\OpeningBundle\Config\GameConfig;

class PlayerController extends Controller
{
    public function rematchAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $data = $this->get('lichess.rematcher')->rematch($player);
        $this->flush();
        if ($data) {
            list($game, $messages) = $data;
            $this->get('lila')->rematchAccept($player, $game, $messages);
        } else {
            $this->get('lila')->rematchOffer($player->getGame());
        }

        return new Response('ok');
    }

    public function offerDrawAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);

        if ($message = $this->get('lichess.drawer')->offer($player)) {
            $this->flush();
            $this->get('lila')->draw($player, $message);
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function declineDrawOfferAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);

        if ($message = $this->get('lichess.drawer')->decline($player)) {
            $this->flush();
            $this->get('lila')->draw($player, $message);
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function cancelDrawOfferAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);

        if ($message = $this->get('lichess.drawer')->cancel($player)) {
            $this->flush();
            $this->get('lila')->draw($player, $message);
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function showAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $game = $player->getGame();

        if ($player->getIsAi()) {
            throw new NotFoundHttpException('Can not show AI player');
        } elseif ($player->getUser() && $player->getUser() != $this->getAuthenticatedUser()) {
            // protect game against private url sharing
            return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $game->getId(), 'color' => $player->getColor())));
        }
        $this->get('lila')->alive($player);

        if(!$game->getIsStarted()) {
            if ($this->get('lila')->getActivity($player->getOpponent()) > 0) {
                $messages = $this->get('lichess.joiner')->join($player);
                if (!$messages) {
                    return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id)));
                }
                $this->flush();
                $this->get('lila')->join($player, $messages);
            } else {
                return $this->render('LichessBundle:Player:waitOpponent.html.twig', array('player' => $player));
            }
        }

        return $this->render('LichessBundle:Player:show.html.twig', array(
            'player'              => $player,
            'messageHtml'         => $this->get('lila')->renderMessages($game),
            'opponentActivity'    => $this->get('lila')->getActivity($player->getOpponent()),
            'checkSquareKey'      => $game->getCheckSquareKey(),
            'possibleMoves'       => ($player->isMyTurn() && $game->getIsPlayable()) ? $this->get('lila')->possibleMoves($player) : null
        ));
    }

    public function waitFriendAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        if($player->getGame()->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lila')->alive($player);

        $config = new GameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.friend', array()));
        return $this->render('LichessBundle:Player:waitFriend.html.twig', array(
            'player' => $player,
            'config' => $config->createView()
        ));
    }

    public function cancelFriendAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $this->get('doctrine.odm.mongodb.document_manager')->remove($player->getGame());
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_homepage'));
    }

    public function tableAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->get('lichess.provider')->findPlayer($playerFullId);
            $template = $player->getGame()->getIsPlayable() ? 'table' : 'tableEnd';
        }
        else {
            $player = $this->get('lichess.provider')->findPublicPlayer($id, $color);
            $template = 'watchTable';
        }
        return $this->render('LichessBundle:Game:'.$template.'.html.twig', array(
            'player'           => $player,
            'opponentActivity' => $this->get('lila')->getActivity($player->getOpponent())
        ));
    }

    public function opponentAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->get('lichess.provider')->findPlayer($playerFullId);
        } else {
            $player = $this->get('lichess.provider')->findPublicPlayer($id, $color);
        }
        $opponent = $player->getOpponent();

        return $this->opponentPlayerAction($opponent, $playerFullId);
    }

    public function opponentPlayerAction(Player $opponent, $playerFullId)
    {
        if($playerFullId) {
            $template = 'opponent';
        } else {
            $template = 'watchOpponent';
        }
        $opponentActivity = $playerFullId ? $this->get('lila')->getActivity($opponent) : 2;

        return $this->render('LichessBundle:Player:'.$template.'.html.twig', array(
            'opponent'         => $opponent,
            'opponentActivity' => $opponentActivity,
            'game'             => $opponent->getGame(),
            'playerFullId'     => $playerFullId
        ));
    }

    protected function flush($safe = true)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => $safe));
    }

    protected function getAuthenticatedUser()
    {
        return $this->get('security.context')->getToken()->getUser();
    }
}
