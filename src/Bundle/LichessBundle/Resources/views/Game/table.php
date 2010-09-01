<?php $turnPlayer = $player->getGame()->getTurnPlayer() ?>
<?php $opponent = $player->getOpponent() ?>
<div class="lichess_table">
    <div class="lichess_opponent">
        <?php if ($opponent->getIsAi()): ?>
            <span><?php echo $view['translator']->_('Opponent: %ai_name%', array('%ai_name%' => 'Crafty A.I.')) ?></span>
            <?php $selectedLevel = $opponent->getAiLevel() ?>
            <select class="lichess_ai_level">
                <?php for($level=1; $level<9; $level++): ?>
                <option value="<?php echo $level ?>" <?php if($level === $selectedLevel) echo 'selected="selected"' ?>><?php echo $view['translator']->_('Level') ?> <?php echo $level ?>
                <?php endfor; ?>
            </select>    
        <?php else: ?>
            <div class="opponent_status">
              <?php $view['actions']->output('LichessBundle:Player:opponent', array('hash' => $player->getGame()->getHash(), 'color' => $player->getColor(), 'playerFullHash' => $player->getFullHash())) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="lichess_separator"></div>
    <div class="lichess_current_player">
        <div class="lichess_player white <?php echo $turnPlayer->isBlack() ? ' none' : '' ?>">
            <div class="lichess_piece king white"></div>
            <p><?php echo $view['translator']->_($player->isWhite() ? 'Your turn' : 'Waiting') ?></p>
        </div>
        <div class="lichess_player black <?php echo $turnPlayer->isWhite() ? ' none' : '' ?>">
            <div class="lichess_piece king black"></div>
            <p><?php echo $view['translator']->_($player->isBlack() ? 'Your turn' : 'Waiting') ?></p>
        </div>
    </div>
    <div class="lichess_control clearfix">
    <label title="<?php echo $view['translator']->_('Toggle the chat') ?>" class="lichess_enable_chat"><input type="checkbox" checked="checked" /><?php echo $view['translator']->_('Chat') ?></label>
    <a href="<?php echo $view['router']->generate('lichess_resign', array('hash' => $player->getFullHash())) ?>" class="lichess_resign" title="<?php echo $view['translator']->_('Give up') ?>"><?php echo $view['translator']->_('Resign') ?></a>
    </div>
</div>
