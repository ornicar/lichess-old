<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>lichess</title>
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/reset.css" />
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/lichess.css" />
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/layout.css" />
    </head>
    <body>
        <div class="content">
            <h1 class="site_title_wrap">
                <a class="site_title" href="<?php echo $view->router->generate('lichess_homepage') ?>" id="logo">Lichess</a>
            </h1>
            <div id="lichess">
                <?php $view->slots->output('_content') ?>
            </div>
        </div>
        <footer class="footer_wrap">
            <div class="footer">
                <a href="http://github.com/ornicar/lichess">Lichess source code on GitHub</a>
                <br />
                Open Source software built with PHP 5.3,
                <a href="http://symfony-reloaded.org">Symfony 2</a> &amp;
                <a href="http://jqueryui.com/">jQuery UI</a> 
                <br />
                <span id="email"></span>
            </div>
        </footer>
        <script src="/bundle/lichess/js/lib.min.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/ctrl.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/game.js" type="text/javascript"></script>
    </body>
</html>
