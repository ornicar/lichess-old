<?php $game = $player->getGame() ?>
<?php $turnPlayer = $game->getTurnPlayer() ?>
<?php $opponent = $player->getOpponent() ?>
<?php if($game->hasClock()) $view->output('LichessBundle:Game:clock.php', array('clock' => $player->getGame()->getClock(), 'color' => $opponent->getColor())) ?>
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
              <?php $view['actions']->output('LichessBundle:Player:opponent', array('hash' => $game->getHash(), 'color' => $player->getColor(), 'playerFullHash' => $player->getFullHash())) ?>
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
        <a href="<?php echo $view['router']->generate('lichess_resign', array('hash' => $player->getFullHash())) ?>" class="lichess_resign" title="<?php echo $view['translator']->_('Give up') ?>"><?php echo $view['translator']->_('Resign') ?></a>
    </div>
    <?php if($player->isMyTurn() && $game->isThreefoldRepetition()): ?>
    <div class="lichess_claim_draw_zone">
        <?php echo $view['translator']->_('Threefold repetition') ?>.&nbsp;
        <a class="lichess_claim_draw" href="<?php echo $view['router']->generate('lichess_claim_draw', array('hash' => $player->getFullHash())) ?>"><?php echo $view['translator']->_('Claim a draw') ?></a>
    </div>
    <?php endif; ?>
</div>
<?php if($game->hasClock()) $view->output('LichessBundle:Game:clock.php', array('clock' => $player->getGame()->getClock(), 'color' => $player->getColor())) ?>
