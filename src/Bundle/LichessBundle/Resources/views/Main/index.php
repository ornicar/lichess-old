<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game lichess_game_not_started clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => null)) ?>
        <a href="<?php echo $view->router->generate('lichess_homepage', array('color' => $player->getOpponent()->getColor())) ?>" class="lichess_exchange" title="Change position"></a>
    </div> 
    <div class="lichess_table_wrap">
        <div class="lichess_table lichess_table_not_started">
            <a class="lichess_button lichess_toggle_join_url" title="Invite a friend to play with you">Play with a friend</a>
            <div class="lichess_join_url">
                <p>To invite someone to play, give this url:</p>
                <span><?php echo $view->router->generate('lichess_game', array('hash' => $player->getGame()->getHash()), true) ?></span>
            </div>
            <a href="<?php echo $view->router->generate('lichess_invite_ai', array('hash' => $player->getFullHash())) ?>" class="lichess_button" title="Challenge the artificial intelligence">Play with the machine</a>
            <a href="<?php echo $view->router->generate('lichess_play_with_anybody', array('hash' => $player->getFullHash())) ?>" class="lichess_button" title="Pick a random human opponent">Play with anybody</a>
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => null, 'parameters' => $parameters)) ?>
