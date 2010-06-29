<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => $checkSquareKey)) ?>
    </div> 
    <div class="lichess_ground">
        <div class="lichess_table lichess_table_not_started">
            <a class="lichess_button lichess_toggle_join_url">Play with a friend</a>
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:watchData', array('player' => $player, 'parameters' => $parameters)) ?>
