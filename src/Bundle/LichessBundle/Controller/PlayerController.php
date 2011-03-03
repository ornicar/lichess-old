<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Config\AnybodyGameConfig;
use Bundle\LichessBundle\Config\FriendGameConfig;
use Bundle\LichessBundle\Chess\DrawerConcurrentOfferException;
use Bundle\LichessBundle\Chess\FinisherException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RuntimeException;

class PlayerController extends Controller
{
    public function outoftimeAction($id, $version)
    {
        $player = $this->findPlayer($id);
        $this->get('lichess.finisher')->outoftime($player);
        $this->flush();

        return $this->renderJson($this->get('lichess.client_updater')->getEventsSinceClientVersion($player, $version));
    }

    public function rematchAction($id, $version)
    {
        $player = $this->findPlayer($id);
        $this->get('lichess.rematcher')->rematch($player);
        $this->flush();

        return $this->renderJson($this->get('lichess.client_updater')->getEventsSinceClientVersion($player, $version));
    }

    public function syncAction($id, $color, $version, $playerFullId)
    {
        $player = $this->findPublicPlayer($id, $color);
        if($playerFullId) {
            $this->get('lichess_synchronizer')->setAlive($player);
        }
        $player->getGame()->cachePlayerVersions();

        return $this->renderJson($this->get('lichess.client_updater')->getEventsSinceClientVersion($player, $version, (bool) $playerFullId));
    }

    public function forceResignAction($id)
    {
        $this->get('lichess.finisher')->forceResign($this->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function offerDrawAction($id)
    {
        try {
            $this->get('lichess.drawer')->offer($this->findPlayer($id));
        } catch (DrawerConcurrentOfferException $e) {
            return $this->acceptDrawOffer($id);
        }
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function declineDrawOfferAction($id)
    {
        $this->get('lichess.drawer')->decline($this->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function acceptDrawOfferAction($id)
    {
        $this->get('lichess.drawer')->accept($this->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function cancelDrawOfferAction($id)
    {
        $this->get('lichess.drawer')->cancel($this->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function claimDrawAction($id)
    {
        $this->get('lichess.finisher')->claimDraw($this->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function moveAction($id, $version)
    {
        $player = $this->findPlayer($id);
        $eventsSinceClientVersion = $this->get('lichess.mover')->move($player, $version, $this->get('request')->request->all());
        $this->get('lichess.object_manager')->flush(false);

        return $this->renderJson($eventsSinceClientVersion);
    }

    public function showAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        $this->get('lichess_synchronizer')->setAlive($player);
        if(!$game->getIsStarted()) {
            throw new RuntimeException(sprintf('Player:show game:%s, Game not started', $game->getId()), 410);
        }
        $checkSquareKey = $this->get('lichess.analyser_factory')->create($game->getBoard())->getCheckSquareKey($game->getTurnPlayer());

        return $this->render('LichessBundle:Player:show.html.twig', array(
            'player'              => $player,
            'isOpponentConnected' => $this->get('lichess_synchronizer')->isConnected($player->getOpponent()),
            'checkSquareKey'      => $checkSquareKey,
            'possibleMoves'       => ($player->isMyTurn() && $game->getIsPlayable()) ? $analyser->getPlayerPossibleMoves($player, (bool) $checkSquareKey) : null
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
        $message = trim($this->get('request')->get('message'));
        $player = $this->findPlayer($id);
        $this->get('lichess_synchronizer')->setAlive($player);
        $this->get('lichess.messenger')->addPlayerMessage($player, $message);
        $this->get('lichess.object_manager')->flush();

        return $this->renderJson($this->get('lichess.client_updater')->getEventsSinceClientVersion($player, $version));
    }

    public function waitAnybodyAction($id)
    {
        try {
            $player = $this->findPlayer($id);
        }
        catch(NotFoundHttpException $e) {
            return new RedirectResponse($this->generateUrl('lichess_invite_anybody'));
        }
        if($player->getGame()->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess_synchronizer')->setAlive($player);

        $config = new AnybodyGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.anybody', array()));
        return $this->render('LichessBundle:Player:waitAnybody.html.twig', array(
            'player' => $player,
            'config' => $config
        ));
    }

    public function cancelAnybodyAction($id)
    {
        $player = $this->findPlayer($id);
        $game   = $player->getGame();
        if($game->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess.seek_queue')->remove($game);
        $this->get('lichess.object_manager')->flush(array('safe' => true));
        $this->get('lichess.logger')->notice($player, 'Game:inviteAnybody cancel');

        return new RedirectResponse($this->generateUrl('lichess_homepage', array('color' => $player->getColor())));
    }

    public function waitFriendAction($id)
    {
        $player = $this->findPlayer($id);
        if($player->getGame()->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess_synchronizer')->setAlive($player);

        $config = new FriendGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.friend', array()));
        return $this->render('LichessBundle:Player:waitFriend.html.twig', array(
            'player' => $player,
            'config' => $config
        ));
    }

    public function resignAction($id)
    {
        $player = $this->findPlayer($id);
        try {
            $this->get('lichess.finisher')->resign($this->findPlayer($id));
        } catch (FinisherException $e) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function abortAction($id)
    {
        try {
            $this->get('lichess.finisher')->abort($this->findPlayer($id));
        } catch (FinisherException $e) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function aiLevelAction($id)
    {
        $player = $this->findPlayer($id);
        $level = min(8, max(1, (int)$this->get('request')->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this->flush(false);

        return new Response('done');
    }

    public function tableAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->findPlayer($playerFullId);
            $template = $player->getGame()->getIsPlayable() ? 'table' : 'tableEnd';
        }
        else {
            $player = $this->findPublicPlayer($id, $color);
            $template = 'watchTable';
        }
        return $this->render('LichessBundle:Game:'.$template.'.html.twig', array(
            'player'              => $player,
            'isOpponentConnected' => $this->get('lichess_synchronizer')->isConnected($player->getOpponent())
        ));
    }

    public function opponentAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->findPlayer($playerFullId);
            $template = 'opponent';
        }
        else {
            $player = $this->findPublicPlayer($id, $color);
            $template = 'watchOpponent';
        }
        $opponent = $player->getOpponent();
        return $this->render('LichessBundle:Player:'.$template.'.html.twig', array(
            'opponent'            => $opponent,
            'isOpponentConnected' => $playerFullId ? $this->get('lichess_synchronizer')->isConnected($opponent) : true,
            'game'                => $player->getGame(),
            'playerFullId'        => $playerFullId
        ));
    }

    /**
     * Get the player for this id
     *
     * @param string $id
     * @return Player
     */
    protected function findPlayer($id)
    {
        $gameId = substr($id, 0, 8);
        $playerId = substr($id, 8, 12);

        $game = $this->get('lichess.repository.game')->findOneById($gameId);
        if(!$game) {
            throw new NotFoundHttpException('Player:findPlayer Can\'t find game '.$gameId);
        }

        $player = $game->getPlayerById($playerId);
        if(!$player) {
            throw new NotFoundHttpException('Player:findPlayer Can\'t find player '.$playerId);
        }

        return $player;
    }

    /**
     * Get the public player for this id
     *
     * @param string $id
     * @return Player
     */
    protected function findPublicPlayer($id, $color)
    {
        $game = $this->get('lichess.repository.game')->findOneById($id);
        if(!$game) {
            throw new NotFoundHttpException('Player:findPublicPlayer Can\'t find game '.$id);
        }

        $player = $game->getPlayer($color);
        if(!$player) {
            throw new NotFoundHttpException('Player:findPublicPlayer Can\'t find player '.$color);
        }

        return $player;
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }

    protected function flush($safe = true)
    {
        return $this->get('lichess.object_manager')->flush(array('safe' => $safe));
    }
}
