<?php
$game = $player->getGame();
$gameId = $game->getId();
$color = $player->getColor();
$opponent = $player->getOpponent();
$playerFullId = $player->getFullId();
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
        'spectator' => false
    ),
    'opponent' => array(
        'color' => $opponent->getColor(),
        'ai' => $opponent->getIsAi(),
        'connected' => $isOpponentConnected
    ),
    'sync_delay' => $parameters['lichess.synchronizer.delay'] * 1000,
    'animation_delay' => 400,
    'url' => array(
        'sync' => $view['router']->generate('lichess_sync', array('id' => $gameId, 'color' => $color, 'version' => 9999999, 'playerFullId' => $playerFullId)),
        'table' => $view['router']->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId)),
        'opponent' => $view['router']->generate('lichess_opponent', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId)),
        'move' => $view['router']->generate('lichess_move', array('id' => $playerFullId, 'version' => 9999999)),
        'say' => $view['router']->generate('lichess_say', array('id' => $playerFullId, 'version' => 9999999)),
        'ai_level' => $opponent->getIsAi() ? $view['router']->generate('lichess_ai_level', array('id' => $playerFullId)) : null,
        'outoftime' => $game->hasClock() ? $view['router']->generate('lichess_outoftime', array('id' => $playerFullId, 'version' => 9999999)) : null
    ),
    'i18n' => array(
        'Game Over' => $view['translator']->_('Game Over'),
        'Waiting for opponent' => $view['translator']->_('Waiting for opponent'),
        'Your turn' => $view['translator']->_('Your turn'),
    ),
    'possible_moves' => $possibleMoves,
    'debug' => $parameters['kernel.debug']
);
?>
<script type="text/javascript">var lichess_data = <?php echo json_encode($data) ?>;</script>
