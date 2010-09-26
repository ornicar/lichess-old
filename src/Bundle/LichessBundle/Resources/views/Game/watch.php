<?php $view->extend('LichessBundle::layout') ?>
<?php $view['slots']->set('title_suffix', ' #'.$game->getHash()) ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => $checkSquareKey)) ?>
    </div> 
    <div class="lichess_ground">
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player, 'position' => 'top')) ?>
        <div class="lichess_table_wrap">
            <?php $view['actions']->output('LichessBundle:Player:table', array('hash' => $player->getGame()->getHash(), 'color' => $player->getColor(), 'playerFullHash' => '')) ?>
        </div>
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player->getOpponent(), 'position' => 'bottom')) ?>
    </div>
</div>

<?php $view->output('LichessBundle:Game:watchData', array('player' => $player, 'parameters' => $parameters, 'possibleMoves' => $possibleMoves)) ?>

<?php $view['slots']->start('goodies') ?>
<div class="lichess_goodies">
    <a class="lichess_replay_link" target="_blank" href="<?php echo $view['router']->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' =>isset($color) ? $color : 'white')) ?>" title="<?php echo $view['translator']->_('Share this url to let spectators see the game') ?>"><?php echo $view['translator']->_('Replay and analyse') ?></a>
    <br /><br />
    <?php echo $view['translator']->_('You are viewing this game as a spectator') ?>.<br /><br />
    <a href="<?php echo $view['router']->generate('lichess_homepage') ?>"><strong><?php echo $view['translator']->_('Play a new game') ?></strong></a>
</div>
<?php $view['slots']->stop() ?>
