<?php
$game = $player->getGame();
$opponent = $player->getOpponent();
$playerFullHash = $player->getFullHash();
$data = array(
    'game' => array(
        'hash' => $game->getHash(),
        'started' => $game->getIsStarted(),
        'finished' => $game->getIsFinished(),
        'turns' => $game->getTurns(),
    ),
    'player' => array(
        'fullHash' => $playerFullHash,
        'color' => $player->getColor(),
        'version' => $player->getStack()->getVersion()
    ),
    'opponent' => array(
        'color' => $opponent->getColor(),
        'ai' => $opponent->getIsAi(),
        'connected' => $isOpponentConnected
    ),
    'sync_delay' => $parameters['lichess.synchronizer.delay'] * 1000,
    'animation_delay' => 500,
    'url' => array(
        'move' => $view->router->generate('lichess_move', array('hash' => $playerFullHash, 'version' => 0)),
        'sync' => $view->router->generate('lichess_sync', array('hash' => $playerFullHash, 'version' => 0)),
        'say' => $view->router->generate('lichess_say', array('hash' => $playerFullHash, 'version' => 0)),
        'table' => $view->router->generate('lichess_table', array('hash' => $playerFullHash)),
        'opponent' => $view->router->generate('lichess_opponent', array('hash' => $playerFullHash)),
        'ai_level' => $opponent->getIsAi() ? $view->router->generate('lichess_ai_level', array('hash' => $playerFullHash)) : null
    ),
    'time' => time(),
    'i18n' => array(
        'Game Over' => 'Game Over',
        'Waiting for opponent' => 'Waiting for opponent',
        'Your turn' => 'Your turn'
    ),
    'possible_moves' => $possibleMoves
);
?>
<script type="text/javascript">var lichess_data = <?php echo json_encode($data) ?>;</script>
