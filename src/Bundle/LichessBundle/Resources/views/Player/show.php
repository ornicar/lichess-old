<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_game clearfix lichess_player_<?php echo $player->getColor() ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Game:board', array('player' => $player, 'checkSquareKey' => $checkSquareKey)) ?>
    </div> 
    <div class="lichess_table_wrap">
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player, 'position' => 'top')) ?>
        <?php if($player->getGame()->getIsFinished()): ?>
            <?php $view->output('LichessBundle:Game:tableEnd', array('player' => $player)) ?>
        <?php else: ?>
            <?php $view->output('LichessBundle:Game:table', array('player' => $player)) ?>
        <?php endif; ?>
        <?php $view->output('LichessBundle:Game:cemetery', array('player' => $player->getOpponent(), 'position' => 'bottom')) ?>
    </div>
</div>

<?php $view->output('LichessBundle:Game:data', array('player' => $player, 'possibleMoves' => $possibleMoves)) ?>

<?php if(!$player->getOpponent()->getIsAi()): ?>
    <?php $view->slots->set('chat', $view->render('LichessBundle:Chat:room', array('player' => $player))) ?>
<?php endif; ?>
