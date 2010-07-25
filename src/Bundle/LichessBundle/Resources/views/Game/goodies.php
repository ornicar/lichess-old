<div class="lichess_goodies">
    <input class="lichess_hash_input" readonly="readonly" value="<?php echo $view->router->generate('lichess_game', array('hash' => $game->getHash()), true) ?>" title="Share this url to let spectators see the game" />
    <a target="blank" href="<?php echo $view->router->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' => 'white')) ?>"><?php echo $view->translator->_('Replay and analysis') ?></a>
</div>
