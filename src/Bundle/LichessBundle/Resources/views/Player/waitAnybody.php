<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Play with anybody').' - '.$player->getColor()) ?>

<div class="lichess_game lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard_'.$player->getColor().'.php') ?>
        <div class="lichess_overboard wait_anybody">
            <img src="<?php echo $view['assets']->getUrl('/bundles/lichess/images/hloader.gif') ?>" width="220" height="33" /><br />
            <?php echo $view['translator']->_('Waiting for opponent') ?>...
        </div>
    </div>
    <div class="lichess_ground">
        <div class="lichess_table lichess_table_not_started">
        <a href="<?php echo $view['router']->generate('lichess_invite_friend', array('color' => $player->getColor())) ?>" class="lichess_button" title="<?php echo $view['translator']->_('Invite a friend to play with you') ?>"><?php echo $view['translator']->_('Play with a friend') ?></a>
        <a href="<?php echo $view['router']->generate('lichess_invite_ai', array('color' => $player->getColor())) ?>" class="lichess_button" title="<?php echo $view['translator']->_('Challenge the artificial intelligence') ?>"><?php echo $view['translator']->_('Play with the machine') ?></a>
        <span class="lichess_button active"><?php echo $view['translator']->_('Play with anybody') ?></span>
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data.php', array('player' => $player, 'possibleMoves' => null, 'parameters' => $parameters, 'isOpponentConnected' => false)) ?>
