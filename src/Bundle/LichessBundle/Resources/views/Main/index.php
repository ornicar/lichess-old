<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('assets_pack', 'home') ?>
<?php $view['slots']->set('title', 'Lichess') ?>

<div class="lichess_game_not_started clearfix lichess_player_<?php echo $color ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard_'.$color.'.php') ?>
        <a href="<?php echo $view['router']->generate('lichess_homepage', array('color' => 'white' === $color ? 'black' : 'white')) ?>" class="lichess_exchange" title="<?php echo $view['translator']->_('Change side') ?>"></a>
    </div>
    <?php $view->output('LichessBundle:Game:bootGround.php', array('color' => $color)) ?>
</div>

<?php $view['slots']->start('baseline') ?>
Don't register. <strong>Play Chess</strong>.
<?php $view['slots']->stop() ?>
