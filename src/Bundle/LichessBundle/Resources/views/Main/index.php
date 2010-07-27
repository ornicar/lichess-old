<?php $view->extend('LichessBundle::layout') ?>
<?php $view->slots->set('assets_pack', 'home') ?>
<?php $view->slots->set('title', 'Lichess - '.$color) ?>

<div class="lichess_game_not_started clearfix lichess_player_<?php echo $color ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard', array('color' => $color)) ?>
        <a href="<?php echo $view->router->generate('lichess_homepage', array('color' => 'white' === $color ? 'black' : 'white')) ?>" class="lichess_exchange" title="<?php echo $view->translator->_('Change side') ?>"></a>
    </div> 
    <div class="lichess_ground">
        <div class="lichess_table lichess_table_not_started">
            <a href="<?php echo $view->router->generate('lichess_invite_friend', array('color' => $color)) ?>" class="lichess_button" title="<?php echo $view->translator->_('Invite a friend to play with you') ?>"><?php echo $view->translator->_('Play with a friend') ?></a>
            <a href="<?php echo $view->router->generate('lichess_invite_ai', array('color' => $color)) ?>" class="lichess_button" title="<?php echo $view->translator->_('Challenge the artificial intelligence') ?>"><?php echo $view->translator->_('Play with the machine') ?></a>
            <a href="<?php echo $view->router->generate('lichess_invite_anybody', array('color' => $color)) ?>" class="lichess_button" title="<?php echo $view->translator->_('Pick a random human opponent') ?>"><?php echo $view->translator->_('Play with anybody') ?></a>
        </div>
    </div>
</div>
