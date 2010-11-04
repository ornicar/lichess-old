<?php $game = $player->getGame() ?>
<?php if($game->hasClock()) $view->output('LichessBundle:Game:clock.php', array('clock' => $game->getClock(), 'color' => $player->getOpponent()->getColor(), 'position' => 'top')) ?>
<div class="lichess_table <?php $game->getIsFinished() && print 'finished ' ?>spectator">
    <div class="lichess_opponent">
        <?php $view['actions']->output('LichessBundle:Player:opponent', array('hash' => $game->getHash(), 'color' => $player->getColor(), 'playerFullHash' => '')) ?>
    </div>
    <div class="lichess_separator"></div>
    <div class="lichess_current_player">
        <?php if($game->getIsFinished()): ?>
            <?php if($winner = $game->getWinner()): ?>
                <div class="lichess_player <?php echo $winner->getColor() ?>">
                    <div class="lichess_piece king <?php echo $winner->getColor() ?>"></div>
                    <p><?php echo $view['translator']->_($game->getStatusMessage()) ?><br /><?php echo $view['translator']->_(ucfirst($winner->getColor()).' is victorious') ?></p>
                </div>
            <?php else: ?>
                <div class="lichess_player">
                    <p><?php echo $view['translator']->_($game->getStatusMessage()) ?></p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="lichess_player white <?php echo $game->getTurnPlayer()->isBlack() ? ' none' : '' ?>">
                <div class="lichess_piece king white"></div>
                <p><?php echo $view['translator']->_('White plays') ?></p>
            </div>
            <div class="lichess_player black <?php echo $game->getTurnPlayer()->isWhite() ? ' none' : '' ?>">
                <div class="lichess_piece king black"></div>
                <p><?php echo $view['translator']->_('Black plays') ?></p>
            </div>
         <?php endif; ?>
    </div>
    <div class="lichess_separator"></div>
        <?php $view['actions']->output('LichessBundle:Player:opponent', array('hash' => $game->getHash(), 'color' => $player->getOpponent()->getColor(), 'playerFullHash' => '')) ?>
</div>
<?php if($game->hasClock()) $view->output('LichessBundle:Game:clock.php', array('clock' => $game->getClock(), 'color' => $player->getColor(), 'position' => 'bottom')) ?>
