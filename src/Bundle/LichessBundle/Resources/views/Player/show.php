<?php $view->extend('LichessBundle::layout') ?>
<?php $view['slots']->set('robots', 'noindex, nofollow') ?>
<?php $view['slots']->set('title_suffix', ' #'.$player->getHash()) ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?> not_spectator">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => $checkSquareKey)) ?>
    </div> 
    <div class="lichess_ground">
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player, 'position' => 'top')) ?>
        <div class="lichess_table_wrap">
            <?php $view['actions']->output('LichessBundle:Player:table', array('hash' => $player->getGame()->getHash(), 'color' => $player->getColor(), 'playerFullHash' => $player->getFullHash())) ?>
        </div>
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player->getOpponent(), 'position' => 'bottom')) ?>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => $possibleMoves, 'isOpponentConnected' => $isOpponentConnected, 'parameters' => $parameters)) ?>

<?php if(!$player->getOpponent()->getIsAi()): ?>
    <?php $view['slots']->set('chat', $view->render('LichessBundle:Player:room', array('player' => $player))) ?>
<?php endif; ?>

<?php $view['slots']->set('goodies', $view->render('LichessBundle:Game:goodies', array('game' => $player->getGame(), 'color' => $player->getColor()))) ?>
