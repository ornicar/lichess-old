<?php if($isOpponentConnected): ?>
<?php echo $view['translator']->_('Human opponent connected') ?>
<?php elseif($player->getGame()->getIsFinished()): ?>
<?php echo $view['translator']->_('Human opponent offline') ?> 
<?php else: ?>
<?php echo $view['translator']->_('The other player has left the game. You can force resignation, or wait for him.') ?><br />
<a title="<?php echo $view['translator']->_('Make your opponent resign') ?>" class="force_resignation" href="<?php echo $view['router']->generate('lichess_force_resignation', array('hash' => $player->getFullHash())) ?>"><?php echo $view['translator']->_('Force resignation') ?></a>
<?php endif; ?>
