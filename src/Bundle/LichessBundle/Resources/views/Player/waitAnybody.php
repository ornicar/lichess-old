<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Play with anybody').' - '.$player->getColor()) ?>

<div class="lichess_game lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard_'.$player->getColor().'.php') ?>
        <div class="lichess_overboard wait_anybody">
            <img src="<?php echo $view['assets']->getUrl('/bundles/lichess/images/hloader.gif') ?>" width="220" height="33" /><br />
            <?php echo $view['translator']->_('Waiting for opponent') ?>...
            <p class="explanations">
                Variant: <?php echo implode(' or ', $config->getVariantNames()) ?><br />
                Clock: <?php echo implode(' or ', $config->getTimeNames()) ?>
            </p>
            <p class="explanations">
                <?php echo $view['translator']->_('Hold on, we are searching for a game that matches your criterias') ?>
            </p>
        </div>
    </div>
    <?php echo $view->output('LichessBundle:Game:bootGround.php', array('color' => $player->getColor(), 'active' => 'anybody')) ?>
</div>

<?php $view->output('LichessBundle:Game:data.php', array('player' => $player, 'possibleMoves' => null, 'parameters' => $parameters, 'isOpponentConnected' => false)) ?>
