<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Model\Game;
use Bundle\LichessBundle\Form;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PlayerController extends Controller
{
    public function outoftimeAction($id, $version)
    {
        return $this->renderJson($this->get('lichess_service_player')->checkOutOfTime($id, $version));
    }

    public function rematchAction($id)
    {
        $player = $this->get('lichess_service_player')->rematch($id);

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
    }

    public function syncAction($id, $color, $version, $playerFullId)
    {
        $data = $this->get('lichess_service_player')->sync($id, $color, $playerFullId, $version);
        // remove private events if user is spectator
        if(!$playerFullId) {
            foreach($data['e'] as $index => $event) {
                if('message' === $event['type'] || 'redirect' === $event['type']) {
                    unset($data['e'][$index]);
                }
            }
        }

        return $this->renderJson($data);
    }

    public function forceResignAction($id)
    {
        $this->get('lichess_service_player')->resign($id, true);

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function offerDrawAction($id)
    {
        if ($this->get('lichess_service_draw')->offer($id)) {
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
        } else {
            return $this->forward('LichessBundle:Player:acceptDrawOffer', array('id' => $id));
        }
    }

    public function declineDrawOfferAction($id)
    {
        $this->get('lichess_service_draw')->declineOffer($id);

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function acceptDrawOfferAction($id)
    {
        $this->get('lichess_service_draw')->acceptOffer($id);
        
        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function cancelDrawOfferAction($id)
    {
        $this->get('lichess_service_draw')->cancelOffer($id);

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function claimDrawAction($id)
    {
        $this->get('lichess_service_draw')->claimOffer($id);

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function moveAction($id, $version)
    {
        $postData = $this->get('request')->request;
        $data = $this->get('lichess_service_board')->move($id, $postData->get('from'), $postData->get('to'), $postData->get('options', array()), $version);

        return $this->renderJson($data);
    }

    public function showAction($id)
    {
        $player = $this->get('lichess_service_player')->findPlayer($id);
        $game = $player->getGame();

        $this->get('lichess_synchronizer')->setAlive($player);

        if(!$game->getIsStarted()) {
            throw new HttpException(sprintf('Player:show game:%s, Game not started', $game->getId()), 410);
        }

        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        return $this->render('LichessBundle:Player:show.twig', array(
            'player' => $player,
            'isOpponentConnected' => $this->get('lichess_synchronizer')->isConnected($player->getOpponent()),
            'checkSquareKey' => $checkSquareKey,
            'possibleMoves' => ($player->isMyTurn() && !$game->getIsFinished()) ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
        ));
    }

    /**
     * Add a message to the chat room
     */
    public function sayAction($id, $version)
    {
        if('POST' !== $this->get('request')->getMethod()) {
            throw new NotFoundHttpException(sprintf('Player:say game:%s, POST method required', $id));
        }
        
        $data = $this->get('lichess_service_player')->addMessage($id, trim($this->get('request')->get('message')), $version);

        return $this->renderJson($data);
    }

    public function waitAnybodyAction($id)
    {
        try {
            $player = $this->get('lichess_service_player')->findPlayer($id);
        }
        catch(NotFoundHttpException $e) {
            return $this->redirect($this->generateUrl('lichess_invite_anybody'));
        }
        if($player->getGame()->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess_synchronizer')->setAlive($player);

        $config = new Form\AnybodyGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.anybody', array()));
        return $this->render('LichessBundle:Player:waitAnybody.twig', array(
            'player'     => $player,
            'config'     => $config
        ));
    }

    public function cancelAnybodyAction($id)
    {
        $player = $this->get('lichess_service_player')->findPlayer($id);
        $game   = $player->getGame();
        if($game->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess.seek_queue')->remove($game);
        $this->get('lichess.object_manager')->flush();
        $this->get('logger')->notice(sprintf('Game:inviteAnybody cancel game:%s', $game->getId()));

        return $this->redirect($this->generateUrl('lichess_homepage', array('color' => $player->getColor())));
    }

    public function waitFriendAction($id)
    {
        $player = $this->get('lichess_service_player')->findPlayer($id);
        if($player->getGame()->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess_synchronizer')->setAlive($player);

        return $this->render('LichessBundle:Player:waitFriend.twig', array(
            'player'     => $player
        ));
    }

    public function resignAction($id)
    {
        $this->get('lichess_service_player')->resign($id);

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function aiLevelAction($id)
    {
        $this->get('lichess_service_player')->setAiLevel($id, (int)$this->get('request')->get('level'));

        return $this->createResponse('done');
    }

    public function tableAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->get('lichess_service_player')->findPlayer($playerFullId);
            $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
            if($nextPlayerId = $player->getGame()->getNext()) {
                $nextGame = $this->get('lichess_service_player')->findPlayer($nextPlayerId)->getGame();
            }
            else {
                $nextGame = null;
            }
        }
        else {
            $player = $this->get('lichess_service_player')->findPublicPlayer($id, $color);
            $template = 'watchTable';
            $nextGame = null;
        }
        return $this->render('LichessBundle:Game:'.$template.'.twig', array(
            'player'              => $player,
            'isOpponentConnected' => $this->get('lichess_synchronizer')->isConnected($player->getOpponent()),
            'nextGame'            => $nextGame
        ));
    }

    public function opponentAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->get('lichess_service_player')->findPlayer($playerFullId);
            $template = 'opponent';
        }
        else {
            $player = $this->get('lichess_service_player')->findPublicPlayer($id, $color);
            $template = 'watchOpponent';
        }
        $opponent = $player->getOpponent();
        return $this->render('LichessBundle:Player:'.$template.'.twig', array(
            'opponent'            => $opponent,
            'isOpponentConnected' => $playerFullId ? $this->get('lichess_synchronizer')->isConnected($opponent) : true,
            'game'                => $player->getGame(),
            'playerFullId'        => $playerFullId
        ));
    }

    protected function renderJson($data)
    {
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
