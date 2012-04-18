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

    public function rematchCancelAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        if ($this->get('lichess.rematcher')->rematchCancel($player)) {
            $this->flush();
            $this->get('lila')->rematchCancel($player->getGame());
        }

        return new Response('ok');
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

        if(!$game->getIsStarted()) {
            if ($this->get('lila')->getActivity($player->getOpponent())) {
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

        $lilaData = json_decode($this->get('lila')->show($player), true);

        return $this->render('LichessBundle:Player:show.html.twig', array(
            'player'              => $player,
            'messageHtml'         => $lilaData['roomHtml'],
            'version'             => $lilaData['version'],
            'checkSquareKey'      => $game->getCheckSquareKey(),
            'possibleMoves'       => $lilaData['possibleMoves']
        ));
    }

    public function waitFriendAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        if($player->getGame()->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }

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
        return $this->render('LichessBundle:Game:'.$template.'.html.twig', array('player' => $player));
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
