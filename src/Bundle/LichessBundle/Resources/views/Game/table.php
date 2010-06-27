<?php $turnPlayer = $player->getGame()->getTurnPlayer() ?>
<?php $opponent = $player->getOpponent() ?>
<div class="lichess_table">
    <div class="lichess_opponent">
        <?php if ($opponent->getIsAi()): ?>
            <span>Opponent is Crafty A.I.</span>
            <?php $selectedLevel = $opponent->getAiLevel() ?>
            <select class="lichess_ai_level">
                <?php for($level=1; $level<9; $level++): ?>
                <option value="<?php echo $level ?>" <?php if($level === $selectedLevel) echo 'selected="selected"' ?>>Level <?php echo $level ?>
                <?php endfor; ?>
            </select>    
        <?php else: ?>
            <div class="opponent_status">
              <?php $view->output('LichessBundle:Player:opponentStatus', array('player' => $player, 'isOpponentConnected' => $isOpponentConnected)) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="lichess_separator"></div>
    <div class="lichess_current_player">
        <div class="lichess_player white">
        <div class="lichess_piece king white" <?php echo $turnPlayer->isBlack() ? ' none' : '' ?>></div>
            <p><?php echo $player->isWhite() ? 'Your turn' : 'Waiting' ?></p>
        </div>
        <div class="lichess_player black <?php echo $turnPlayer->isWhite() ? ' none' : '' ?>">
            <div class="lichess_piece king black"></div>
            <p><?php echo $player->isBlack() ? 'Your turn' : 'Waiting' ?></p>
        </div>
    </div>
    <div class="lichess_control clearfix">
        <label class="lichess_enable_chat"><input type="checkbox" checked="checked" />Chat</label>
        <label class="lichess_enable_animation"><input type="checkbox" checked="checked" />Animation</label>
        <a class="lichess_resign" title="Give up">Resign</a>
    </div>
</div>
