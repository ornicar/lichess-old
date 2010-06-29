<?php
$game = $player->getGame();
$gameHash = $game->getHash();
$color = $player->getColor();
$opponent = $player->getOpponent();
$data = array(
    'game' => array(
        'hash' => $game->getHash(),
        'started' => $game->getIsStarted(),
        'finished' => $game->getIsFinished(),
        'turns' => $game->getTurns(),
    ),
    'player' => array(
        'color' => $player->getColor(),
        'version' => $player->getStack()->getVersion()
    ),
    'opponent' => array(
        'color' => $opponent->getColor(),
        'ai' => $opponent->getIsAi()
    ),
    'sync_delay' => $parameters['lichess.synchronizer.delay'] * 1000,
    'animation_delay' => 500,
    'url' => array(
        'sync' => $view->router->generate('lichess_sync', array('hash' => $gameHash, 'color' => $color, 'version' => 0)),
        'table' => $view->router->generate('lichess_table', array('hash' => $gameHash, 'color' => $color)),
        'opponent' => $view->router->generate('lichess_opponent', array('hash' => $gameHash, 'color' => $color))
    ),
    'i18n' => array(
        'Game Over' => 'Game Over',
        'Waiting for opponent' => 'Waiting for opponent',
        'Your turn' => 'Your turn'
    )
);
?>
<script type="text/javascript">var lichess_data = <?php echo json_encode($data) ?>;</script>
