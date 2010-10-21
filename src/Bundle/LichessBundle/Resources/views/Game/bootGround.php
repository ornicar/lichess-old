<?php $links = array(
    'friend' => array(
        $view['translator']->_('Play with a friend'),
        $view['router']->generate('lichess_invite_friend', array('color' => $color))
    ),
    'anybody' => array(
        $view['translator']->_('Play with anybody'),
        $view['router']->generate('lichess_invite_anybody', array('color' => $color))
    ),
    'ai' => array(
        $view['translator']->_('Play with the machine'),
        $view['router']->generate('lichess_invite_ai', array('color' => $color))
    )
) ?>
<div class="lichess_ground">
    <div class="lichess_table lichess_table_not_started">
        <?php foreach($links as $name => $link): ?>
            <?php if(isset($active) && $name == $active): ?>
                <span class="lichess_button active"><?php echo $link[0] ?></span>
            <?php else: ?>
                <a class="lichess_button" href="<?php echo $link[1] ?>"><?php echo $link[0] ?></a>
            <?php endif ?>
        <?php endforeach ?>
    </div>
</div>
