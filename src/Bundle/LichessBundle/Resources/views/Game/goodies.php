<div class="lichess_goodies">
    <?php echo $view->translator->_('Public permalink') ?> 
    <input class="lichess_hash_input" readonly="readonly" value="<?php echo $view->router->generate('lichess_game', array('hash' => $game->getHash()), true) ?>" title="Share this url to let spectators see the game" />
</div>
