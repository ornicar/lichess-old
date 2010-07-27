<?php
    $view->assets->setVersion(19);
    $view->stylesheets->add('bundle/lichess/css/lib.min.css');
    $view->stylesheets->add('bundle/lichess/css/lichess.css');

    $view->javascripts->add('bundle/lichess/js/lib.min.js');
    $view->javascripts->add('bundle/lichess/js/ctrl.js');
    $assetsPack = $view->slots->get('assets_pack');
    if('home' === $assetsPack) {
    }
    elseif('analyse' === $view->slots->get('assets_pack')) {
        $view->javascripts->add('bundle/lichess/vendor/pgn4web/pgn4web.min.js');
        $view->javascripts->add('bundle/lichess/vendor/transform/jquery.transform.min.js');
        $view->javascripts->add('bundle/lichess/js/analyse.js');
        $view->stylesheets->add('bundle/lichess/css/analyse.css');
        $view->stylesheets->add('bundle/lichess/vendor/pgn4web/fonts/pgn4web-fonts.css');
    }
    else {
        $view->javascripts->add('bundle/lichess/js/game.js');
    }
    if($view->translator->getLocale() !== 'en'):
        $view->javascripts->add('http://static.addtoany.com/menu/locale/'.$view->translator->getLocale().'.js');
    endif;
?>
<!DOCTYPE html>
<html lang="<?php echo $view->session->getLocale() ?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Language" content="<?php echo $view->session->getLocale() ?>">
        <title><?php $view->slots->output('title', 'Lichess') ?> | free online Chess game</title>
        <meta content="Free online Chess game. Easy and fast: no registration, no ads, no flash. Play Chess with computer, friends or random opponent. OpenSource software, uses PHP 5.3, Symfony2 and jQuery 1.4" name="description">
        <meta content="Chess, Chess game, play Chess, online Chess, free Chess, quick Chess, anonymous Chess, opensource, artificial intelligence" name="keywords">
        <meta content="<?php echo $view->slots->get('robots', 'index, follow') ?>" name="robots">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <?php echo $view->stylesheets ?>
    </head>
    <body>
        <div class="content">
            <div class="header">
                <h1>
                    <a class="site_title" href="<?php echo $view->router->generate('lichess_homepage') ?>">Lichess</a>
                </h1>
                <div class="lichess_language">
                    <span><?php echo $view->translator->getLocaleName() ?></span>
                    <ul class="lichess_language_links">
                        <?php foreach($view->translator->getOtherLocales() as $code => $name): ?>
                            <li><a href="<?php echo $view->router->generate('lichess_locale', array('locale' => $code)) ?>"><?php echo $name ?></a></li>
                        <?php endforeach ?>
                        <li><a href="<?php echo $view->router->generate('lichess_translate') ?>">Help translate Lichess!</a></li>
                    </ul>
                </div>
                <div class="lichess_goodies_wrap">
                    <?php $view->slots->output('goodies', '') ?>
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
            <ul class="lichess_social">
            </ul>
            <div class="footer">
                <div class="right">
                    Brought to you by <a title="A french web agency who loves Symfony" href="http://www.knplabs.com/" target="_blank">knpLabs</a><br />
                    <?php echo $view->translator->_('Contact') ?>: <span class="js_email"></span><br />
                    <a href="<?php echo $view->router->generate('lichess_about') ?>" target="_blank">Learn more about Lichess</a>
                </div>
                Get <a href="http://github.com/ornicar/lichess" target="_blank" title="See what's inside, fork and contribute">source code</a> or give <a class="lichess_uservoice" title="Having a suggestion, feature request or bug report? Let me know">feedback</a> or <a href="<?php echo $view->router->generate('lichess_translate') ?>">help translate Lichess</a><br />
                <?php echo $view->translator->_('Open Source software built with %php%, %symfony% and %jqueryui%', array('%php%' => 'PHP 5.3', '%symfony%' => '<a href="http://symfony-reloaded.org" target="_blank">Symfony2</a>', '%jqueryui%' => '<a href="http://jqueryui.com/" target="_blank">jQuery UI</a>')) ?><br />
            <?php echo $view->translator->_('Artificial intelligence') ?>: <a href="http://www.craftychess.com/" target="_blank">Crafty</a>
            </div>
        </div>
        <div title="Come on, make my server suffer :)" class="lichess_server">
            <?php $loadAverage = sys_getloadavg() ?>
            <?php echo $view->translator->_('Server load') ?>: <span class="value"><?php echo round(100*$loadAverage[1]) ?></span>%
        </div>
        <?php echo $view->javascripts ?>
    </body>
</html>
