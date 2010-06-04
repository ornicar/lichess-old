<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player)) ?>
        <a href="<?php echo $view->router->generate('lichess_homepage', array('color' => $player->getOpponent()->getColor())) ?>" class="lichess_exchange" title="Change position"></a>
    </div> 
    <div class="lichess_table_wrap">
        cemetary
        panel
        cemetary
    </div>
</div>
