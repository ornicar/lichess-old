<?php
$game = $player->getGame();
$gameId = $game->getId();
$color = $player->getColor();
$opponent = $player->getOpponent();
$data = array(
    'game' => array(
        'id' => $game->getId(),
        'started' => $game->getIsStarted(),
        'finished' => $game->getIsFinished(),
        'clock' => $game->hasClock(),
        'player' => $game->getTurnPlayer()->getColor(),
        'turns' => $game->getTurns()
    ),
    'player' => array(
        'color' => $player->getColor(),
        'version' => $player->getStack()->getVersion(),
        'spectator' => true
    ),
    'opponent' => array(
        'color' => $opponent->getColor(),
        'ai' => $opponent->getIsAi(),
        'connected' => true
    ),
    'sync_delay' => $parameters['lichess.synchronizer.delay'] * 1000,
    'animation_delay' => 500,
    'url' => array(
        'sync' => $view['router']->generate('lichess_sync', array('id' => $gameId, 'color' => $color, 'version' => 9999999, 'playerFullId' => '')).'/',
        'table' => $view['router']->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => '')).'/',
        'opponent' => $view['router']->generate('lichess_opponent', array('id' => $gameId, 'color' => $color, 'playerFullId' => '')).'/'
    ),
    'i18n' => array(
        'Game Over' => $view['translator']->_('Game Over'),
        'Waiting for opponent' => $view['translator']->_('Waiting for opponent'),
        'Your turn' => $view['translator']->_('Your turn')
    ),
    'possible_moves' => $possibleMoves
);
?>
<script type="text/javascript">var lichess_data = <?php echo json_encode($data) ?>;</script>
