<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_box">
    <h1 class="lichess_title">Page not found (404)</h1>
    <img src="/bundle/lichess/images/sprite.png" alt="Open source Chess game" />
    <p>There is nothing to see here. If you think it's a bug, please send a mail to <span class="js_email"></span></p>
    <a class="lichess_new_game" href="<?php echo $view->router->generate('lichess_homepage') ?>">Start a new game</a>
</div>
