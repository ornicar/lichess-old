<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => null)) ?>
        <a href="<?php echo $view->router->generate('lichess_homepage', array('color' => $player->getOpponent()->getColor())) ?>" class="lichess_exchange" title="Change position"></a>
    </div> 
    <div class="lichess_table_wrap">
        <div class="lichess_table lichess_table_not_started">
            <a class="lichess_button lichess_toggle_join_url">Play with a friend</a>
            <div class="lichess_join_url">
                <p>To invite someone to play, give this url:</p>
                <span><?php echo $view->router->generate('lichess_game', array('hash' => $player->getGame()->getHash()), true) ?></span>
            </div>
            <div class="lichess_join_ai">
                <a href="<?php echo $view->router->generate('lichess_invite_ai', array('hash' => $player->getFullHash())) ?>" class="lichess_button">Play with the machine</a>
            </div>
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => null)) ?>
