<div class="lichess_goodies">
    <a class="lichess_replay_link blank_if_play" href="<?php echo $view['router']->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' =>isset($color) ? $color : 'white')) ?>"><?php echo $view['translator']->_('Replay and analyse') ?></a>
    <div class="lichess_share_game">
        <?php $shareUrl = $view['router']->generate('lichess_game', array('hash' => $game->getHash()), true); ?>
        Share: <a class="lichess_share_url blank_if_play" href="<?php echo $shareUrl ?>" title="<?php echo $view['translator']->_('Share this url to let spectators see the game') ?>"><?php echo $shareUrl ?></a>
    </div>
</div>
