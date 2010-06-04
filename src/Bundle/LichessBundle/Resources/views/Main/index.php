<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player)) ?>
    </div> 
    <div class="lichess_table_wrap">
        cemetary
        panel
        cemetary
    </div>
</div>
