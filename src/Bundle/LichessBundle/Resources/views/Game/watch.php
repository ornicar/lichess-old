<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => $checkSquareKey)) ?>
    </div> 
    <div class="lichess_ground">
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player, 'position' => 'top')) ?>
        <div class="lichess_table_wrap">
            <?php $view->actions->output('LichessBundle:Player:table', array('path' => array('hash' => $player->getGame()->getHash(), 'color' => $player->getColor(), 'playerFullHash' => ''))) ?>
        </div>
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player->getOpponent(), 'position' => 'bottom')) ?>
    </div>
</div>

<?php $view->output('LichessBundle:Game:watchData', array('player' => $player, 'parameters' => $parameters, 'possibleMoves' => $possibleMoves)) ?>

<?php $view->slots->start('goodies') ?>
<div class="lichess_goodies">
<?php echo $view->translator->translate('You are viewing this game as a spectator') ?>.<br /><br />
<a href="<?php echo $view->router->generate('lichess_homepage') ?>"><strong><?php echo $view->translator->translate('Play a new game') ?></strong></a>
</div>
<?php $view->slots->stop() ?>
