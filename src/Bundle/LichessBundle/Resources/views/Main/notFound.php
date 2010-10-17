<?php $view->extend('LichessBundle::layout.php') ?>

<div class="lichess_box">
    <h1 class="lichess_title"><?php echo $view['translator']->_('Page not found') ?> (404)</h1>
    <img src="<?php echo $view['assets']->getUrl('/bundles/lichess/images/sprite.png') ?>" alt="Open source online Chess game" />
    <p><?php echo $view['translator']->_("There is nothing to see here. If you think it's a bug, you could send an email to %email%", array('%email%' => '<span class="js_email"></span>')) ?></p>
    <a class="lichess_new_game" href="<?php echo $view['router']->generate('lichess_homepage') ?>"><?php echo $view['translator']->_('Play a new game') ?></a>
</div>
