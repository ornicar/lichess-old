<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Lichess | Open source PHP, CSS and Javascript free online Chess game, for Symfony 2 and jQuery 1.4</title>
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/reset.css" />
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/lichess.css" />
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/layout.css" />
        <meta content="Free online Chess game. Easy and fast: no registration, no flash; just sit and play. Open source software, it uses PHP 5.3, Symfony 2 and jQuery 1.4" name="description">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <meta name="google-site-verification" content="fZ08Imok7kcLaGcJg7BKQExO6vXGgSgsJUsW6JalUCo" />
    </head>
    <body>
        <div class="content">
            <header class="header">
                <h1>
                    <a class="site_title" href="<?php echo $view->router->generate('lichess_homepage') ?>">Lichess</a>
                </h1>
                <div class="lichess_chat_wrap">
                    <?php $view->slots->output('chat', '') ?>
                </div>
            </header>
            <div id="lichess">
                <?php $view->slots->output('_content') ?>
            </div>
        </div>
        <footer class="footer_wrap">
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
        </footer>
        <script src="/bundle/lichess/js/lib.min.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/ctrl.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/game.js" type="text/javascript"></script>
        <script type="text/javascript">
if(document.domain == 'lichess.org') {
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-7935029-3']);
_gaq.push(['_trackPageview']);
(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = 'http://www.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
}
        </script>
    </body>
</html>
