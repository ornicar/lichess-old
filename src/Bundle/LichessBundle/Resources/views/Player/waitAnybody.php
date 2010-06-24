<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game lichess_game_not_started clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => null)) ?>
    </div> 
    <div class="lichess_table_wrap">
        <div class="lichess_table_wait_anybody">
            Waiting for an opponent
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => null)) ?>
