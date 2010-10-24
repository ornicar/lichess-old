<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Ai\Crafty;
use Bundle\LichessBundle\Ai\Stupid;
use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PlayerController extends Controller
{
    public function outoftimeAction($hash, $version)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if($game->checkOutOfTime()) {
            $events = array(array('type' => 'end'), array('type' => 'possible_moves', 'possible_moves' => null));
            $player->getStack()->addEvents($events);
            $opponent->getStack()->addEvents($events);
            $this['lichess_persistence']->save($game);
            $this['logger']->notice(sprintf('Game:outoftime game:%s', $game->getHash()));
        }

        $this['logger']->warn(sprintf('Game:outoftime finished game:%s', $game->getHash()));
        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function rematchAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if(!$game->getIsFinished()) {
            $this['logger']->warn(sprintf('Game:rematch not finished game:%s', $game->getHash()));
            return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
        }

        if($nextPlayerHash = $game->getNext()) {
            $nextOpponent = $this->findPlayer($nextPlayerHash);
            if($nextOpponent->getColor() == $player->getColor()) {
                $nextGame = $nextOpponent->getGame();
                $nextPlayer = $nextOpponent->getOpponent();
                if(!$nextGame->getIsStarted()) {
                    $nextGame->setRoom(clone $game->getRoom());
                    if($game->hasClock()) {
                        $nextGame->setClock(clone $game->getClock());
                    }
                    $nextGame->start();
                    $this['lichess_persistence']->save($nextGame);
                    $opponent->getStack()->addEvent(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('hash' => $nextOpponent->getFullHash()))));
                    $this['lichess_persistence']->save($game);
                    if($this['lichess_synchronizer']->isConnected($opponent)) {
                        $this['lichess_synchronizer']->setAlive($nextOpponent);
                    }
                    $this['logger']->notice(sprintf('Game:rematch join game:%s', $nextGame->getHash()));
                }
                else {
                    $this['logger']->warn(sprintf('Game:rematch join already started game:%s', $nextGame->getHash()));
                }
                return $this->redirect($this->generateUrl('lichess_player', array('hash' => $nextPlayer->getFullHash())));
            }
        }
        else {
            $nextPlayer = $this->container->getLichessGeneratorService()->createReturnGame($player);
            $this['lichess_persistence']->save($nextPlayer->getGame());
            $opponent->getStack()->addEvent(array('type' => 'reload_table'));
            $this['lichess_synchronizer']->setAlive($player);
            $this['logger']->notice(sprintf('Game:rematch proposal for game:%s', $game->getHash()));
            $this['lichess_persistence']->save($game);
        }

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
    }

    public function syncAction($hash, $color, $version, $playerFullHash)
    {
        $player = $this->findPublicPlayer($hash, $color);
        if($playerFullHash) {
            $this['lichess_synchronizer']->setAlive($player);
            $this['lichess_persistence']->save($player->getGame());
        }
        $data = $this->getPlayerSyncData($player, $version);
        // remove private events if user is spectator
        if(!$playerFullHash) {
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
        $isOpponentConnected = $this['lichess_synchronizer']->isConnected($player->getOpponent());
        $currentPlayerColor = $game->getTurnPlayer()->getColor();
        try {
            $events = $version != $clientVersion ? $this['lichess_synchronizer']->getDiffEvents($player, $clientVersion) : array();
        }
        catch(\OutOfBoundsException $e) {
            $events = array(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('hash' => $player->getFullHash()))));
        }

        $data = array('v' => $version, 'o' => $isOpponentConnected, 'e' => $events, 'p' => $currentPlayerColor);
        $data['ncp'] = $this['lichess_synchronizer']->getNbConnectedPlayers();
        if($game->hasClock()) {
            $data['c'] = $game->getClock()->getRemainingTimes();
        }

        return $data;
    }

    public function forceResignAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        if(!$game->getIsFinished() && $this['lichess_synchronizer']->isTimeout($player->getOpponent())) {
            $game->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
            $player->getStack()->addEvent(array('type' => 'end'));
            $player->getOpponent()->getStack()->addEvent(array('type' => 'end'));
            $this['lichess_persistence']->save($game);
            $this['logger']->notice(sprintf('Game:forceResign game:%s', $game->getHash()));
        }
        else {
            $this['logger']->warn(sprintf('Game:forceResign FAIL game:%s', $game->getHash()));
        }

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
    }

    public function claimDrawAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        if(!$game->getIsFinished() && $game->isThreefoldRepetition() && $player->isMyTurn()) {
            $game->setStatus(GAME::DRAW);
            $player->getStack()->addEvent(array('type' => 'end'));
            $player->getOpponent()->getStack()->addEvent(array('type' => 'end'));
            $this['lichess_persistence']->save($game);
            $this['logger']->notice(sprintf('Game:claimDraw game:%s', $game->getHash()));
        }
        else {
            $this['logger']->warn(sprintf('Game:claimDraw FAIL game:%s', $game->getHash()));
        }

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
    }

    protected function renderJson($data)
    {
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function moveAction($hash, $version)
    {
        $player = $this->findPlayer($hash);
        $this['lichess_synchronizer']->setAlive($player);
        $game = $player->getGame();
        if(!$player->isMyTurn()) {
            throw new \LogicException(sprintf('Game:move not my turn game:%s', $game->getHash()));
        }
        $opponent = $player->getOpponent();
        $postData = $this['request']->request;
        $move = $postData->get('from').' '.$postData->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game, $stack);
        $opponentPossibleMoves = $manipulator->play($move, $postData->get('options', array()));
        $player->getStack()->addEvents($stack->getEvents());
        $player->getStack()->addEvent(array('type' => 'possible_moves', 'possible_moves' => null));
        $response = $this->renderJson($this->getPlayerSyncData($player, $version));

        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $stack->reset();
                $possibleMoves = $manipulator->play($this->container->getLichessAiService()->move($game, $opponent->getAiLevel()));
                $player->getStack()->addEvents($stack->getEvents());
                $player->getStack()->addEvent(array('type' => 'possible_moves', 'possible_moves' => $possibleMoves));
            }
        }
        else {
            $opponent->getStack()->addEvents($stack->getEvents());
            $opponent->getStack()->addEvent(array('type' => 'possible_moves', 'possible_moves' => $opponentPossibleMoves));
        }
        $this['lichess_persistence']->save($game);

        return $response;
    }

    public function showAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        $this['lichess_synchronizer']->setAlive($player);

        if(!$game->getIsStarted()) {
            throw new HttpException(sprintf('Player:show game:%s, Game not started', $game->getHash()), 410);
        }

        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        return $this->render('LichessBundle:Player:show.php', array(
            'player' => $player,
            'isOpponentConnected' => $this['lichess_synchronizer']->isConnected($player->getOpponent()),
            'checkSquareKey' => $checkSquareKey,
            'parameters' => $this->container->getParameterBag()->all(),
            'possibleMoves' => ($player->isMyTurn() && !$game->getIsFinished()) ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
        ));
    }

    /**
     * Add a message to the chat room
     */
    public function sayAction($hash, $version)
    {
        if('POST' !== $this['request']->getMethod()) {
            throw new NotFoundHttpException(sprintf('Player:say game:%s, POST method required', $game->getHash()));
        }
        $message = trim($this['request']->get('message'));
        if('' === $message) {
            throw new NotFoundHttpException(sprintf('Player:say game:%s, No message', $game->getHash()));
        }
        $message = substr($message, 0, 140);
        $player = $this->findPlayer($hash);
        $this['lichess_synchronizer']->setAlive($player);
        $player->getGame()->getRoom()->addMessage($player->getColor(), $message);
        $htmlMessage = \Bundle\LichessBundle\Helper\TextHelper::autoLink(htmlentities($message, ENT_COMPAT, 'UTF-8'));
        $sayEvent = array(
            'type' => 'message',
            'html' => sprintf('<li class="%s">%s</li>', $player->getColor(), $htmlMessage)
        );
        $player->getStack()->addEvent($sayEvent);
        $player->getOpponent()->getStack()->addEvent($sayEvent);
        $this['lichess_persistence']->save($player->getGame());

        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function waitAnybodyAction($hash)
    {
        try {
            $player = $this->findPlayer($hash);
        }
        catch(NotFoundHttpException $e) {
            return $this->redirect($this->generateUrl('lichess_invite_anybody'));
        }
        if($player->getGame()->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
        }
        $this['lichess_synchronizer']->setAlive($player);

        return $this->render('LichessBundle:Player:waitAnybody.php', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
    }

    public function waitFriendAction($hash)
    {
        $player = $this->findPlayer($hash);
        if($player->getGame()->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
        }
        $this['lichess_synchronizer']->setAlive($player);

        return $this->render('LichessBundle:Player:waitFriend.php', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
    }

    public function resignAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        if($game->getIsFinished()) {
            $this['logger']->warn(sprintf('Player:resign finished game:%s', $game->getHash()));
            return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
        }
        $opponent = $player->getOpponent();

        $game->setStatus(Game::RESIGN);
        $opponent->setIsWinner(true);
        $player->getStack()->addEvent(array('type' => 'end'));
        $opponent->getStack()->addEvent(array('type' => 'end'));
        $this['lichess_persistence']->save($game);
        $this['logger']->notice(sprintf('Player:resign game:%s', $game->getHash()));

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
    }

    public function aiLevelAction($hash)
    {
        $player = $this->findPlayer($hash);
        $level = min(8, max(1, (int)$this['request']->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this['lichess_persistence']->save($player->getGame());
        return $this->createResponse('done');
    }

    public function tableAction($hash, $color, $playerFullHash)
    {
        if($playerFullHash) {
            $player = $this->findPlayer($playerFullHash);
            $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
            if($nextPlayerHash = $player->getGame()->getNext()) {
                $nextGame = $this->findPlayer($nextPlayerHash)->getGame();
            }
            else {
                $nextGame = null;
            }
        }
        else {
            $player = $this->findPublicPlayer($hash, $color);
            $template = 'watchTable';
            $nextGame = null;
        }
        return $this->render('LichessBundle:Game:'.$template.'.php', array('player' => $player, 'isOpponentConnected' => $this['lichess_synchronizer']->isConnected($player->getOpponent()), 'nextGame' => $nextGame));
    }

    public function opponentAction($hash, $color, $playerFullHash)
    {
        if($playerFullHash) {
            $player = $this->findPlayer($playerFullHash);
            $template = 'opponent';
        }
        else {
            $player = $this->findPublicPlayer($hash, $color);
            $template = 'watchOpponent';
        }
        return $this->render('LichessBundle:Player:'.$template.'.php', array('player' => $player, 'isOpponentConnected' => $this['lichess_synchronizer']->isConnected($player->getOpponent())));
    }

    /**
     * Get the player for this hash
     *
     * @param string $hash
     * @return Player
     */
    protected function findPlayer($hash)
    {
        $gameHash = substr($hash, 0, 6);
        $playerHash = substr($hash, 6, 10);

        $game = $this['lichess_persistence']->find($gameHash);
        if(!$game) {
            throw new NotFoundHttpException('Player:findPlayer Can\'t find game '.$gameHash);
        }

        $player = $game->getPlayerByHash($playerHash);
        if(!$player) {
            throw new NotFoundHttpException('Player:findPlayer Can\'t find player '.$playerHash);
        }

        return $player;
    }

    /**
     * Get the public player for this hash
     *
     * @param string $hash
     * @return Player
     */
    protected function findPublicPlayer($hash, $color)
    {
        $game = $this['lichess_persistence']->find($hash);
        if(!$game) {
            throw new NotFoundHttpException('Player:findPublicPlayer Can\'t find game '.$gameHash);
        }

        $player = $game->getPlayer($color);
        if(!$player) {
            throw new NotFoundHttpException('Player:findPublicPlayer Can\'t find player '.$color);
        }

        return $player;
    }
}
