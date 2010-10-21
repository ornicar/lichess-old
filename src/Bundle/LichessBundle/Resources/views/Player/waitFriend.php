<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Play with a friend').' - '.$player->getColor()) ?>

<div class="lichess_game lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard_'.$player->getColor().'.php') ?>
        <div class="lichess_overboard">
            <?php echo $view['translator']->_('To invite someone to play, give this url') ?>:
            <input title="<?php echo $view['translator']->_('The first person who uses this url will start to play with you') ?>" class="lichess_hash_input" readonly="readonly" value="<?php echo $view['router']->generate('lichess_game', array('hash' => $player->getGame()->getHash()), true) ?>" />
        </div>
    </div>
    <?php echo $view->output('LichessBundle:Game:bootGround.php', array('color' => $player->getColor(), 'active' => 'friend')) ?>
</div>

<?php $view->output('LichessBundle:Game:data.php', array('player' => $player, 'possibleMoves' => null, 'parameters' => $parameters, 'isOpponentConnected' => false)) ?>
