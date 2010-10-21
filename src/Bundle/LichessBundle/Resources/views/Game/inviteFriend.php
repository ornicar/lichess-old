<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Play with a friend').' - '.$color) ?>

<div class="lichess_game lichess_game_not_started waiting_opponent clearfix lichess_player_<?php echo $color ?>">
    <div class="lichess_board_wrap">
        <?php $view->output('LichessBundle:Main:staticBoard_'.$color.'.php') ?>
        <div class="lichess_overboard">
            <h2>Play with a friend</h2>
            <div class="clock_config_form">
                <form action="<?php echo $view['router']->generate('lichess_invite_friend', array('color' => $color)) ?>" method="post">
                    <?php foreach($form['times'] as $time): ?>
                        <div><?php echo $time->widget() ?></div>
                    <?php endforeach ?>
                    <button type="submit" class="submit">Start</button>
                </form>
            </div>
        </div>
    </div>
    <?php $view->output('LichessBundle:Game:bootGround.php', array('color' => $color, 'active' => 'friend')) ?>
</div>
