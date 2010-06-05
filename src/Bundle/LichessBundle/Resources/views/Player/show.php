<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player)) ?>
    </div> 
    <div class="lichess_table_wrap">
        <div class="lichess_table">
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player)) ?>
