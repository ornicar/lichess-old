<?php if($isOpponentConnected): ?>
Human opponent connected
<?php elseif($player->getGame()->getIsFinished()): ?>
Human opponent offline
<?php else: ?>
The other player has left the game. You can force resignation to end up the game.
<a class="force_resignation" href="<?php echo $view->router->generate('lichess_force_resignation', array('hash' => $player->getFullHash())) ?>">Force resignation</a>
<?php endif; ?>
