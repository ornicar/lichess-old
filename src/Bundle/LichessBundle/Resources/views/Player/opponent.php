<?php if($isOpponentConnected): ?>
<?php echo $view->translator->translate('Human opponent connected') ?>
<?php elseif($player->getGame()->getIsFinished()): ?>
<?php echo $view->translator->translate('Human opponent offline') ?> 
<?php else: ?>
<?php echo $view->translator->translate('The other player has left the game. You can force resignation, or wait for him.') ?><br />
<a title="<?php echo $view->translator->translate('Make your opponent resign') ?>" class="force_resignation" href="<?php echo $view->router->generate('lichess_force_resignation', array('hash' => $player->getFullHash())) ?>"><?php echo $view->translator->translate('Force resignation') ?></a>
<?php endif; ?>
