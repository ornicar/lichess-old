<?php echo $view->translator->translate(ucfirst($player->getOpponent()->getColor())) ?> - 
<?php if($player->getOpponent()->getIsAi()): ?>
<?php echo $view->translator->translate('%ai_name% level %ai_level%', array('%ai_name%' => 'Crafty A.I.', '%ai_level%' => $player->getOpponent()->getAiLevel())) ?>
<?php else: ?>
<?php echo $view->translator->translate('Human') ?> 
<?php endif; ?>
