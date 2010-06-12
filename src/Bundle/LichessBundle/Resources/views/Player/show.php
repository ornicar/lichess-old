<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => $checkSquareKey)) ?>
    </div> 
    <div class="lichess_table_wrap">
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player, 'position' => 'top')) ?>
        <div class="lichess_table">
            <div class="lichess_opponent">
             <?php echo $player->getOpponent()->getIsAi() ? 'Opponent is Crafty A.I.' : 'Human opponent' ?>
            </div>
            <div class="lichess_separator"></div>
            <div class="lichess_current_player">
                <div class="lichess_player white">
                    <div class="lichess_piece king white"></div>
                    <p><?php echo $player->isWhite() ? 'Your turn' : 'Waiting for opponent' ?></p>
                </div>
                <div class="lichess_player black">
                    <div class="lichess_piece king black"></div>
                    <p><?php echo $player->isBlack() ? 'Your turn' : 'Waiting for opponent' ?></p>
                </div>
            </div>
            <div class="lichess_control clearfix">
                <a class="lichess_permalink_toggle">Save</a>
                <a class="lichess_resign" title="Give up" href="<?php echo $view->router->generate('lichess_resign', array('hash' => $player->getFullHash())) ?>">Resign</a>
            </div>
            <div class="lichess_permalink">
                To continue later, keep this url:
                <span><?php echo $view->router->generate('lichess_player', array('hash' => $player->getFullHash())) ?>
            </div>
        </div>
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player->getOpponent(), 'position' => 'bottom')) ?>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => $possibleMoves)) ?>
