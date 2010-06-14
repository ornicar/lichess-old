<?php $view->extend('LichessBundle::layout') ?>

<div class="lichess_box">
    <h1 class="lichess_title">This game has 2 players</h1>
    <img src="/bundle/lichess/images/sprite.png" alt="Open source Chess game" />
    <p>You cannot join this game, because it is already started!</p>
    <a href="<?php echo $view->router->generate('lichess_homepage') ?>">Start a new game</a>
</div>
