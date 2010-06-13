<?php
$game = $player->getGame();
$playerFullHash = $player->getFullHash();
$baseUrl = 'http://'.$_SERVER['HTTP_HOST'].'/';
$data = array(
    'game' => array(
        'hash' => $game->getHash(),
        'started' => $game->getIsStarted(),
        'finished' => $game->getIsFinished(),
        'turns' => $game->getTurns(),
        'updatedAt' => $game->getUpdatedAt()
    ),
    'player' => array(
        'hash' => $player->getHash(),
        'fullHash' => $playerFullHash,
        'color' => $player->getColor()
    ),
    'opponent' => array(
        'color' => $player->getOpponent()->getColor()
    ),
    'beat' => array(
        'delay' => 2000,
    ),
    'url' => array(
        'socket' => '/socket/'.$playerFullHash.'.json',
        'move' => $view->router->generate('lichess_move', array('hash' => $playerFullHash)),
        'ai_level' => $player->getOpponent()->getIsAi() ? $view->router->generate('lichess_ai_level', array('hash' => $playerFullHash)) : null
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
