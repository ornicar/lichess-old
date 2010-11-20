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
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            $this->get('lichess.object_manager')->flush();
            $this->get('logger')->notice(sprintf('Player:outoftime game:%s', $game->getId()));
        }

        $this->get('logger')->warn(sprintf('Player:outoftime finished game:%s', $game->getId()));
        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function rematchAction($id)
    {
        $player = $this->findPlayer($id);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if(!$game->getIsFinished()) {
            $this->get('logger')->warn(sprintf('Player:rematch not finished game:%s', $game->getId()));
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
        }

        if($nextPlayerId = $game->getNext()) {
            $nextOpponent = $this->findPlayer($nextPlayerId);
            if($nextOpponent->getColor() === $player->getColor()) {
                $nextGame = $nextOpponent->getGame();
                $nextPlayer = $nextOpponent->getOpponent();
                if(!$nextGame->getIsStarted()) {
                    $nextGame->setRoom(clone $game->getRoom());
                    if($game->hasClock()) {
                        $nextGame->setClock(clone $game->getClock());
                    }
                    $nextGame->start();
                    $opponent->addEventToStack(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('id' => $nextOpponent->getFullId()))));
                    $this->get('lichess.object_manager')->flush();
                    if($this->get('lichess_synchronizer')->isConnected($opponent)) {
                        $this->get('lichess_synchronizer')->setAlive($nextOpponent);
                    }
                    $this->get('logger')->notice(sprintf('Player:rematch join game:%s', $nextGame->getId()));
                }
                else {
                    $this->get('logger')->warn(sprintf('Player:rematch join already started game:%s', $nextGame->getId()));
                }
                return $this->redirect($this->generateUrl('lichess_player', array('id' => $nextPlayer->getFullId())));
            }
        }
        else {
            $nextPlayer = $this->container->getLichessGeneratorService()->createReturnGame($player);
            $this->get('lichess.object_manager')->persist($nextPlayer->getGame());
            $opponent->addEventToStack(array('type' => 'reload_table'));
            $this->get('lichess_synchronizer')->setAlive($player);
            $this->get('logger')->notice(sprintf('Player:rematch proposal for game:%s', $game->getId()));
            $this->get('lichess.object_manager')->flush();
        }

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
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
            $events = array(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('id' => $player->getFullId()))));
        }

        $data = array('v' => $version, 'o' => $isOpponentConnected, 'e' => $events, 'p' => $currentPlayerColor, 't' => $game->getTurns());
        $data['ncp'] = $this->get('lichess_synchronizer')->getNbConnectedPlayers();
        if($game->hasClock()) {
            $data['c'] = $game->getClock()->getRemainingTimes();
        }

        return $data;
    }

    public function forceResignAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if(!$game->getIsFinished() && $this->get('lichess_synchronizer')->isTimeout($player->getOpponent())) {
            $game->setStatus(Game::TIMEOUT);
            $game->setWinner($player);
            $this->get('lichess_finisher')->finish($game);
            $player->addEventToStack(array('type' => 'end'));
            $player->getOpponent()->addEventToStack(array('type' => 'end'));
            $this->get('lichess.object_manager')->flush();
            $this->get('logger')->notice(sprintf('Player:forceResign game:%s', $game->getId()));
        }
        else {
            $this->get('logger')->warn(sprintf('Player:forceResign FAIL game:%s', $game->getId()));
        }

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function claimDrawAction($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if(!$game->getIsFinished() && $game->isThreefoldRepetition() && $player->isMyTurn()) {
            $game->setStatus(GAME::DRAW);
            $this->get('lichess_finisher')->finish($game);
            $player->addEventToStack(array('type' => 'end'));
            $player->getOpponent()->addEventToStack(array('type' => 'end'));
            $this->get('lichess.object_manager')->flush();
            $this->get('logger')->notice(sprintf('Player:claimDraw game:%s', $game->getId()));
        }
        else {
            $this->get('logger')->warn(sprintf('Player:claimDraw FAIL game:%s', $game->getId()));
        }

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    protected function renderJson($data)
    {
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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
                    $this->get('logger')->err(sprintf('Player:move Crafty game:%s, variant:%s, turn:%d - %s %s', $game->getId(), $game->getVariantName(), $game->getTurns(), get_class($e), $e->getMessage()));
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
            if($cheater = $this->get('lichess.anticheat')->detectCheater($game)) {
                $game->setStatus(Game::CHEAT);
                $game->setWinner($cheater->getOpponent());
                $cheater->addEventToStack(array('type' => 'end'));
                $cheater->getOpponent()->addEventToStack(array('type' => 'end'));
            }
        }
        if($game->getIsFinished()) {
            $this->get('lichess_finisher')->finish($game);
            $this->get('logger')->notice(sprintf('Player:move finish game:%s, %s', $game->getId(), $game->getStatusMessage()));
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
        $message = trim($this->get('request')->get('message'));
        if('' === $message) {
            throw new NotFoundHttpException(sprintf('Player:say game:%s, No message', $id));
        }
        if(mb_strlen($message) > 140) {
            throw new NotFoundHttpException(sprintf('Player:say game:%s, too long message', $id));
        }
        $player = $this->findPlayer($id);
        $this->get('lichess_synchronizer')->setAlive($player);
        $player->getGame()->getRoom()->addMessage($player->getColor(), $message);
        $htmlMessage = \Bundle\LichessBundle\Helper\TextHelper::autoLink(htmlentities($message, ENT_COMPAT, 'UTF-8'));
        $sayEvent = array(
            'type' => 'message',
            'html' => sprintf('<li class="%s">%s</li>', $player->getColor(), $htmlMessage)
        );
        $player->addEventToStack($sayEvent);
        $player->getOpponent()->addEventToStack($sayEvent);
        $this->get('lichess.object_manager')->flush();

        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function waitAnybodyAction($id)
    {
        try {
            $player = $this->findPlayer($id);
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

    public function waitFriendAction($id)
    {
        $player = $this->findPlayer($id);
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
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($game->getIsFinished()) {
            $this->get('logger')->warn(sprintf('Player:resign finished game:%s', $game->getId()));
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $opponent = $player->getOpponent();

        $game->setStatus(Game::RESIGN);
        $game->setWinner($opponent);
        $this->get('lichess_finisher')->finish($game);
        $player->addEventToStack(array('type' => 'end'));
        $opponent->addEventToStack(array('type' => 'end'));
        $this->get('lichess.object_manager')->flush();
        $this->get('logger')->notice(sprintf('Player:resign game:%s', $game->getId()));

        return $this->redirect($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function aiLevelAction($id)
    {
        $player = $this->findPlayer($id);
        $level = min(8, max(1, (int)$this->get('request')->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this->get('lichess.object_manager')->flush();

        return $this->createResponse('done');
    }

    public function tableAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->findPlayer($playerFullId);
            $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
            if($nextPlayerId = $player->getGame()->getNext()) {
                $nextGame = $this->findPlayer($nextPlayerId)->getGame();
            }
            else {
                $nextGame = null;
            }
        }
        else {
            $player = $this->findPublicPlayer($id, $color);
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
            $player = $this->findPlayer($playerFullId);
            $template = 'opponent';
        }
        else {
            $player = $this->findPublicPlayer($id, $color);
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
