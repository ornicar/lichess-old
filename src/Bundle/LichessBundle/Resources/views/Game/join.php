<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Play with a friend').' - '.$color) ?>

<div class="lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $color ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard_'.$color.'.php') ?>
        <div class="lichess_overboard">
            <h2><?php echo $view['translator']->_('Join the game') ?></h2>
            <br />
            <a class="join_redirect_url" href="<?php echo $view['router']->generate('lichess_join_game', array('hash' => $game->getHash())) ?>">Enjoy!</a>
        </div>
    </div>
</div>

<script type="text/javascript">
window.location.href = "<?php echo $view['router']->generate('lichess_join_game', array('hash' => $game->getHash())) ?>"
</script>
