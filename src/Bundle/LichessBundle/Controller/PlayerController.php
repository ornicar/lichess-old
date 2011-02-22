<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Form;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PlayerController extends Controller
{
    public function outoftimeAction($id, $version)
    {
        $player = $this->findPlayer($id);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if($game->checkOutOfTime()) {
            $this->get('lichess_finisher')->finish($game);
            $events = array(array('type' => 'end'), array('type' => 'possible_moves', 'possible_moves' => null));
            $player->addEventsToStack($events);
            $opponent->addEventsToStack($events);
            $this->get('lichess.object_manager')->flush(array('safe' => true));
            $this->get('lichess.logger')->notice($player, 'Player:outoftime');
        } else {
            throw new \LogicException($this->get('lichess.logger')->formatPlayer($player, 'Player:outoftime'));
        }
        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function rematchAction($id, $version)
    {
        $player = $this->findPlayer($id);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if(!$player->canOfferRematch()) {
            throw new \LogicException($this->get('lichess.logger')->formatPlayer($player, 'Player:rematch'));
        }
        elseif(!$opponent->getIsOfferingRematch()) {
            $this->get('lichess.logger')->notice($player, 'Player:rematch offer');
            $this->get('lichess.messenger')->addSystemMessage($game, 'Rematch offer sent');
            $player->setIsOfferingRematch(true);
            $game->addEventToStacks(array('type' => 'reload_table'));
        } else {
            $this->get('lichess.logger')->notice($player, 'Player:rematch accept');
            $this->get('lichess.messenger')->addSystemMessage($game, 'Rematch offer accepted');
            $nextOpponent = $this->get('lichess_generator')->createReturnGame($opponent);
            $nextPlayer = $nextOpponent->getOpponent();
            $nextGame = $nextOpponent->getGame();
            $nextGame->start();
            foreach(array(array($player, $nextPlayer), array($opponent, $nextOpponent)) as $pair) {
                $this->get('lichess_synchronizer')->setAlive($pair[1]);
                $pair[0]->addEventToStack(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('id' => $pair[1]->getFullId()))));
            }
            $this->get('lichess.object_manager')->persist($nextGame);
        }
        $this->get('lichess.object_manager')->flush(array('safe' => true));

        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function syncAction($id, $color, $version, $playerFullId)
    {
        $player = $this->findPublicPlayer($id, $color);
        if($playerFullId) {
            $this->get('lichess_synchronizer')->setAlive($player);
        }
        $player->getGame()->cachePlayerVersions();
        $data = $this->getPlayerSyncData($player, $version);
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

    protected function getPlayerSyncData($player, $clientVersion)
    {
        $game = $player->getGame();
        $version = $player->getStack()->getVersion();
        $isOpponentConnected = $this->get('lichess_synchronizer')->isConnected($player->getOpponent());
        $currentPlayerColor = $game->getTurnColor();
        try {
            $events = $version != $clientVersion ? $this->get('lichess_synchronizer')->getDiffEvents($player, $clientVersion) : array();
        }
        catch(\OutOfBoundsException $e) {
            $this->get('lichess.logger')->warn($player, 'Player:syncData OutOfBounds');
            $events = array(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('id' => $player->getFullId()))));
        }
        // render system messages
        foreach($events as $index => $event) {
            if('message' === $event['type']) {
                $events[$index]['html'] = $this->get('lichess.html.twig.extension')->roomMessage($event['message']);
                unset($events[$index]['message']);
            }
        }

        $data = array('v' => $version, 'o' => $isOpponentConnected, 'e' => $events, 'p' => $currentPlayerColor, 't' => $game->getTurns());
        if($game->hasClock()) {
            $data['c'] = $game->getClock()->getRemainingTimes();
        }

        return $data;
    }

    public function forceResignAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($game->getIsPlayable() && $this->get('lichess_synchronizer')->isTimeout($player->getOpponent())) {
            $game->setStatus(Game::TIMEOUT);
            $game->setWinner($player);
            $this->get('lichess_finisher')->finish($game);
            $game->addEventToStacks(array('type' => 'end'));
            $this->get('lichess.object_manager')->flush(array('safe' => true));
            $this->get('lichess.logger')->notice($player, 'Player:forceResign');
        }
        else {
            $this->get('lichess.logger')->warn($player, 'Player:forceResign');
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function offerDrawAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($game->getIsPlayable()) {
            if(!$player->getIsOfferingDraw()) {
                if($player->getOpponent()->getIsOfferingDraw()) {
                    return $this->forward('LichessBundle:Player:acceptDrawOffer', array('id' => $id));
                }
                $this->get('lichess.messenger')->addSystemMessage($game, 'Draw offer sent');
                $player->setIsOfferingDraw(true);
                $player->getOpponent()->addEventToStack(array('type' => 'reload_table'));
                $this->get('lichess.object_manager')->flush(array('safe' => true));
                $this->get('lichess.logger')->notice($player, 'Player:offerDraw');
            } else {
                $this->get('lichess.logger')->warn($player, 'Player:offerDraw already offered');
            }
        } else {
            $this->get('lichess.logger')->warn($player, 'Player:offerDraw on finished game');
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function declineDrawOfferAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $this->get('lichess.messenger')->addSystemMessage($game, 'Draw offer declined');
            $player->getOpponent()->setIsOfferingDraw(false);
            $player->getOpponent()->addEventToStack(array('type' => 'reload_table'));
            $this->get('lichess.object_manager')->flush(array('safe' => true));
            $this->get('lichess.logger')->notice($player, 'Player:declineDrawOffer');
        } else {
            $this->get('lichess.logger')->warn($player, 'Player:declineDrawOffer no offered draw');
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function acceptDrawOfferAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $this->get('lichess.messenger')->addSystemMessage($game, 'Draw offer accepted');
            $game->setStatus(GAME::DRAW);
            $this->get('lichess_finisher')->finish($game);
            $game->addEventToStacks(array('type' => 'end'));
            $this->get('lichess.object_manager')->flush(array('safe' => true));
            $this->get('lichess.logger')->notice($player, 'Player:acceptDrawOffer');
        } else {
            $this->get('lichess.logger')->warn($player, 'Player:acceptDrawOffer no offered draw');
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function cancelDrawOfferAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($player->getIsOfferingDraw()) {
            $this->get('lichess.messenger')->addSystemMessage($game, 'Draw offer canceled');
            $player->setIsOfferingDraw(false);
            $player->getOpponent()->addEventToStack(array('type' => 'reload_table'));
            $this->get('lichess.object_manager')->flush(array('safe' => true));
            $this->get('lichess.logger')->notice($player, 'Player:cancelDrawOffer');
        } else {
            $this->get('lichess.logger')->warn($player, 'Player:cancelDrawOffer no offered draw');
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function claimDrawAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($game->getIsPlayable() && $game->isThreefoldRepetition() && $player->isMyTurn()) {
            $game->setStatus(GAME::DRAW);
            $this->get('lichess_finisher')->finish($game);
            $game->addEventToStacks(array('type' => 'end'));
            $this->get('lichess.object_manager')->flush(array('safe' => true));
            $this->get('lichess.logger')->notice($player, 'Player:claimDraw');
        }
        else {
            $this->get('lichess.logger')->warn($player, 'Player:claimDraw FAIL');
        }

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }

    public function moveAction($id, $version)
    {
        $player = $this->findPlayer($id);
        $this->get('lichess_synchronizer')->setAlive($player);
        $game = $player->getGame();
        if(!$player->isMyTurn()) {
            throw new \LogicException(sprintf('Player:move not my turn game:%s', $game->getId()));
        }
        $opponent = $player->getOpponent();
        $isGameAbortable = $game->getIsAbortable();
        $canOfferDraw = $player->canOfferDraw();
        $postData = $this->get('request')->request;
        $move = $postData->get('from').' '.$postData->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game, $stack);
        $opponentPossibleMoves = $manipulator->play($move, $postData->get('options', array()));
        $player->addEventsToStack($stack->getEvents());
        $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => null));
        $response = $this->renderJson($this->getPlayerSyncData($player, $version));

        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $stack->reset();
                $ai = $this->get('lichess_ai');
                try {
                    $possibleMoves = $manipulator->play($ai->move($game, $opponent->getAiLevel()));
                }
                catch(\Exception $e) {
                    $this->get('lichess.logger')->err($player, sprintf('Player:move Crafty %s %s', get_class($e), $e->getMessage()));
                    $ai = $this->get('lichess_ai_fallback');
                    $possibleMoves = $manipulator->play($ai->move($game, $opponent->getAiLevel()));
                }
                $player->addEventsToStack($stack->getEvents());
                $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $possibleMoves));
            }
        }
        else {
            $opponent->addEventsToStack($stack->getEvents());
            $opponent->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $opponentPossibleMoves));
            // Detect if someone uses an AI to cheat on this game, and act
            if($cheater = $this->get('lichess.cheat.internal_detector')->detectCheater($game)) {
                $game->setStatus(Game::CHEAT);
                $game->setWinner($cheater->getOpponent());
                $game->addEventToStacks(array('type' => 'end'));
            }
        }
        if($game->getIsFinished()) {
            $this->get('lichess_finisher')->finish($game);
            $this->get('lichess.logger')->notice($player, 'Player:move finish');
        }
        if($isGameAbortable != $game->getIsAbortable() || $canOfferDraw != $player->canOfferDraw()) {
            $game->addEventToStacks(array('type' => 'reload_table'));
        }
        $this->get('lichess.object_manager')->flush();

        return $response;
    }

    public function showAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();

        $this->get('lichess_synchronizer')->setAlive($player);

        if(!$game->getIsStarted()) {
            throw new \RuntimeException(sprintf('Player:show game:%s, Game not started', $game->getId()), 410);
        }

        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        return $this->render('LichessBundle:Player:show.html.twig', array(
            'player' => $player,
            'isOpponentConnected' => $this->get('lichess_synchronizer')->isConnected($player->getOpponent()),
            'checkSquareKey' => $checkSquareKey,
            'possibleMoves' => ($player->isMyTurn() && $game->getIsPlayable()) ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
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

        return $this->renderJson($this->getPlayerSyncData($player, $version));
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

        $config = new Form\AnybodyGameConfig();
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

        $config = new Form\FriendGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.friend', array()));
        return $this->render('LichessBundle:Player:waitFriend.html.twig', array(
            'player' => $player,
            'config' => $config
        ));
    }

    public function resignAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if(!$game->isResignable()) {
            $this->get('lichess.logger')->warn($player, 'Player:resign non-resignable');
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $opponent = $player->getOpponent();

        $game->setStatus(Game::RESIGN);
        $game->setWinner($opponent);
        $this->get('lichess_finisher')->finish($game);
        $game->addEventToStacks(array('type' => 'end'));
        $this->get('lichess.object_manager')->flush(array('safe' => true));
        $this->get('lichess.logger')->notice($player, 'Player:resign');

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function abortAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if(!$game->getIsAbortable()) {
            $this->get('lichess.logger')->warn($player, 'Player:abort non-abortable');
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $game->setStatus(Game::ABORTED);
        $this->get('lichess_finisher')->finish($game);
        $game->addEventToStacks(array('type' => 'end'));
        $this->get('lichess.object_manager')->flush(array('safe' => true));
        $this->get('lichess.logger')->notice($player, 'Player:abort');

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function aiLevelAction($id)
    {
        $player = $this->findPlayer($id);
        $level = min(8, max(1, (int)$this->get('request')->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this->get('lichess.object_manager')->flush();

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
}
