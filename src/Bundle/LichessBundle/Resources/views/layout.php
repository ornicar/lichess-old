<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Lichess | Open Source Chess game</title>
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/reset.css" />
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/lichess.5.css" />
        <meta content="Free online Chess game. Easy and fast: no registration, no flash; just sit and play. Open Source software, uses PHP 5.3, Symfony 2 and jQuery 1.4" name="description">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <meta name="google-site-verification" content="fZ08Imok7kcLaGcJg7BKQExO6vXGgSgsJUsW6JalUCo" />
    </head>
    <body>
        <div class="content">
            <div class="header">
                <h1>
                    <a class="site_title" href="<?php echo $view->router->generate('lichess_homepage') ?>">Lichess</a>
                </h1>
                <div class="lichess_social">
                    <a href="http://twitter.com/home?status=<?php echo urlencode('Amazing Chess Game, free and Open Source! http://lichess.org/') ?>" class="lichess_tweet" target="blank">Tweet</a>
                    <iframe class="lichess_facebook" src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Flichess.org%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=28" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
                </div>
                <div class="lichess_chat_wrap">
                    <?php $view->slots->output('chat', '') ?>
                </div>
            </div>
            <div id="lichess">
                <?php $view->slots->output('_content') ?>
            </div>
        </div>
        <div class="footer_wrap">
            <div class="footer">
                <div class="right">
                    Brought to you by <a title="A french web agency who loves Symfony" href="http://www.knplabs.com/">knpLabs</a><br />
                    Contact: <span class="js_email"></span><br />
                    <a href="<?php echo $view->router->generate('lichess_about') ?>">Learn more about Lichess</a>
                </div>
                <a href="http://github.com/ornicar/lichess">Lichess source code on GitHub</a><br />
                Open Source software built with PHP 5.3, <a href="http://symfony-reloaded.org">Symfony 2</a> &amp; <a href="http://jqueryui.com/">jQuery UI</a><br />
                Artificial intelligence: <a href="http://www.craftychess.com/">Crafty</a>
            </div>
        </div>
        <script src="/bundle/lichess/js/lib.min.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/ctrl.2.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/game.4.js" type="text/javascript"></script>
    </body>
</html>
