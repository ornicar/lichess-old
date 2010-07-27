<div class="lichess_goodies">
    <a class="lichess_replay_link" target="_blank" href="<?php echo $view->router->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' =>isset($color) ? $color : 'white')) ?>" title="<?php echo $view->translator->_('Share this url to let spectators see the game') ?>"><?php echo $view->translator->_('Replay and analyse') ?></a>
</div>
