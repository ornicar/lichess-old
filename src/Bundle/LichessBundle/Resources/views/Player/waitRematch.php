<?php $view->extend('LichessBundle::layout') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Waiting for opponent').' - '.$player->getColor()) ?>

<div class="lichess_game lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard', array('color' => $player->getColor())) ?>
        <div class="lichess_overboard wait_next">
            <img src="/bundle/lichess/images/hloader.gif" width="220" height="33" /><br />
            <?php echo $view['translator']->_('Waiting for opponent') ?> 
        </div>
    </div> 
    <div class="lichess_ground">
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => null, 'parameters' => $parameters, 'isOpponentConnected' => false)) ?>
