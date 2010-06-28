<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Lichess | Open Source Chess game</title>
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/reset.css" />
        <link type="text/css" rel="stylesheet" href="/bundle/lichess/css/lichess.10.css" />
        <meta content="Free online Chess game. Easy and fast: no registration, no flash; just sit and play. Open Source software, uses PHP 5.3, Symfony2 and jQuery 1.4" name="description">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <meta name="google-site-verification" content="fZ08Imok7kcLaGcJg7BKQExO6vXGgSgsJUsW6JalUCo" />
    </head>
    <body>
        <div class="content">
            <div class="header">
                <h1>
                    <a class="site_title" href="<?php echo $view->router->generate('lichess_homepage') ?>">Lichess</a><span class="lichess_version">1.3</span>
                </h1>
                <div class="lichess_social">
                    <a href="http://twitter.com/home?status=<?php echo urlencode('Amazing Chess Game, free and Open Source! http://lichess.org/') ?>" class="lichess_tweet" target="blank">Tweet</a>
                    <iframe class="lichess_facebook" src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Flichess.org%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe>
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
                    Brought to you by <a title="A french web agency who loves Symfony" href="http://www.knplabs.com/" target="_blank">knpLabs</a><br />
                    Contact: <span class="js_email"></span><br />
                    <a href="<?php echo $view->router->generate('lichess_about') ?>" target="_blank">Learn more about Lichess</a>
                </div>
                <a class="lichess_uservoice" title="Having a suggestion, feature request or bug report? Let me know">Feedback</a> /
                <a href="http://www.pledgie.com/campaigns/11352" target="_blank" title="Lichess is free, support it!">Donate</a> /
                <a href="http://github.com/ornicar/lichess" target="_blank" title="See what's inside, fork and contribute">Source code</a><br />
                Open Source software built with PHP 5.3, <a href="http://symfony-reloaded.org" target="_blank">Symfony2</a> &amp; <a href="http://jqueryui.com/" target="_blank">jQuery UI</a><br />
                Artificial intelligence: <a href="http://www.craftychess.com/" target="_blank">Crafty</a>
            </div>
        </div>
        <div class="lichess_server">
            <?php $loadAverage = sys_getloadavg() ?>
            Server load: <span class="value"><?php echo round(100*$loadAverage[1]) ?></span>%
        </div>
        <script src="/bundle/lichess/js/lib.2.min.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/ctrl.7.js" type="text/javascript"></script>
        <script src="/bundle/lichess/js/game.9.js" type="text/javascript"></script>
    </body>
</html>
