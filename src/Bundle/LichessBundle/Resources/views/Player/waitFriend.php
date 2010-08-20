<?php $view->extend('LichessBundle::layout') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Play with a friend').' - '.$player->getColor()) ?>

<div class="lichess_game lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard', array('color' => $player->getColor())) ?>
        <div class="lichess_overboard">
            <?php echo $view['translator']->_('To invite someone to play, give this url') ?>:
            <input title="<?php echo $view['translator']->_('The first person who uses this url will start to play with you') ?>" class="lichess_hash_input" readonly="readonly" value="<?php echo $view['router']->generate('lichess_game', array('hash' => $player->getGame()->getHash()), true) ?>" />
        </div>
    </div> 
    <div class="lichess_ground">
        <div class="lichess_table lichess_table_not_started">
        <span class="lichess_button active"><?php echo $view['translator']->_('Play with a friend') ?></span>
        <a href="<?php echo $view['router']->generate('lichess_invite_ai', array('color' => $player->getColor())) ?>" class="lichess_button" title="<?php echo $view['translator']->_('Challenge the artificial intelligence') ?>"><?php echo $view['translator']->_('Play with the machine') ?></a>
        <a href="<?php echo $view['router']->generate('lichess_invite_anybody', array('color' => $player->getColor())) ?>" class="lichess_button" title="<?php echo $view['translator']->_('Pick a random human opponent') ?>"><?php echo $view['translator']->_('Play with anybody') ?></a>
        </div>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => null, 'parameters' => $parameters, 'isOpponentConnected' => false)) ?>
