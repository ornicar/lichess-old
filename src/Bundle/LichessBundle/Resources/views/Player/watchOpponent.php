<?php echo $player->getOpponent()->getColor() ?>:
<?php if($player->getOpponent()->getIsAi()): ?>
Crafty A.I. level <?php echo $player->getOpponent()->getAiLevel() ?>
<?php else: ?>
Human
<?php endif; ?>
