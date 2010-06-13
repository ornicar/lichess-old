<?php $winner = $player->getGame()->getWinner() ?>
<div class="lichess_table finished">
    <div class="lichess_opponent">
     <?php echo $player->getOpponent()->getIsAi() ? 'Opponent is Crafty A.I.' : 'Human opponent' ?>
    </div>
    <div class="lichess_separator"></div>
    <div class="lichess_current_player">
        <div class="lichess_player <?php echo $winner->getColor() ?>">
            <div class="lichess_piece king <?php echo $winner->getColor() ?>"></div>
            <p><?php echo $winner->getColor() ?> is victorious</p>
        </div>
    </div>
    <div class="lichess_control clearfix">
        <a class="lichess_new_game" href="<?php echo $view->router->generate('lichess_homepage') ?>">New game</a>
    </div>
</div>
