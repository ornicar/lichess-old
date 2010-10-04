<?php $game = $player->getGame() ?>
<?php $winner = $game->getWinner() ?>
<?php $opponent = $player->getOpponent() ?>
<div class="lichess_table finished <?php echo $game->getNext() ? ' lichess_table_next' : '' ?>">
    <div class="lichess_opponent">
        <?php if ($opponent->getIsAi()): ?>
            <span><?php echo $view['translator']->_('Opponent: %ai_name% level %ai_level%', array('%ai_name%' => 'Crafty A.I.', '%ai_level%' => $opponent->getAiLevel())) ?></span>
        <?php else: ?>
            <div class="opponent_status">
              <?php $view['actions']->output('LichessBundle:Player:opponent', array('hash' => $game->getHash(), 'color' => $player->getColor(), 'playerFullHash' => $player->getFullHash())) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="lichess_separator"></div>
    <div class="lichess_current_player">
        <?php if($winner): ?>
            <div class="lichess_player <?php echo $winner->getColor() ?>">
                <div class="lichess_piece king <?php echo $winner->getColor() ?>"></div>
                <p><?php echo $view['translator']->_($game->getStatusMessage()) ?><br /><?php echo $view['translator']->_(ucfirst($winner->getColor()).' is victorious') ?></p>
            </div>
        <?php else: ?>
            <div class="lichess_player">
                <p><?php echo $view['translator']->_('Stalemate') ?></p>
            </div>
        <?php endif; ?>
    </div>
    <div class="lichess_control clearfix">
        <a class="lichess_new_game" href="<?php echo $view['router']->generate('lichess_homepage') ?>"><?php echo $view['translator']->_('New game') ?></a>
        <?php if(!$opponent->getIsAi()): ?>
            <?php if(isset($nextGame)): ?>
                    <div class="lichess_separator"></div>
                <?php if($player->getColor() == $nextGame->getCreator()->getColor()): ?>
                    <div class="lichess_play_again_join">
<?php echo $view['translator']->_('Your opponent wants to play a new game with you') ?>.&nbsp;
<a class="lichess_play_again" title="<?php echo $view['translator']->_('Play with the same opponent again') ?>" href="<?php echo $view['router']->generate('lichess_rematch', array('hash' => $player->getFullHash())) ?>"><?php echo $view['translator']->_('Join the game') ?></a>
                    </div>
                <?php else: ?>
                    <div class="lichess_play_again_join">
                        <?php echo $view['translator']->_('Rematch proposal sent') ?>.<br />
                        <?php echo $view['translator']->_('Waiting for opponent') ?>...
                    </div>
                <?php endif; ?>
            <?php else: ?>
                | <a class="lichess_rematch" title="<?php echo $view['translator']->_('Play with the same opponent again') ?>" href="<?php echo $view['router']->generate('lichess_rematch', array('hash' => $player->getFullHash())) ?>"><?php echo $view['translator']->_('Rematch') ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
