<?php
    $view->stylesheets->add('bundle/lichess/css/lib.min.css');
    $view->stylesheets->add('bundle/lichess/css/lichess.css');

    $view->javascripts->add('bundle/lichess/js/lib.min.js');
    $view->javascripts->add('bundle/lichess/js/ctrl.js');
    $view->javascripts->add('bundle/lichess/js/game.js');
    if($view->translator->getLocale() !== 'en'):
        $view->javascripts->add('http://static.addtoany.com/menu/locale/'.$view->translator->getLocale().'.js');
    endif;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Lichess | Web Chess game</title>
        <meta content="Free online Chess game. Easy and fast: no registration, no ads, no flash. Play Chess with computer, friends or random opponent. OpenSource software, uses PHP 5.3, Symfony2 and jQuery 1.4" name="description">
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
                        <li><a href="<?php echo $view->router->generate('lichess_translation') ?>">Help translate Lichess!</a></li>
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
                <li class="lichess_stumbleupon"><iframe src="http://www.stumbleupon.com/badge/embed/2/?url=http://lichess.org/"></iframe></li>
                <li class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Flichess.org%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></li>
                <li class="lichess_add2any"><a class="a2a_dd" href="http://www.addtoany.com/share_save?linkurl=http%3A%2F%2Flichess.org%2F&amp;linkname=Best%20web%20Chess%20game%20ever!"><img src="http://static.addtoany.com/buttons/share_save_171_16.png" width="171" height="16" alt="Share/Bookmark"/></a></li>
            </ul>
            <div class="footer">
                <div class="right">
                    Brought to you by <a title="A french web agency who loves Symfony" href="http://www.knplabs.com/" target="_blank">knpLabs</a><br />
                    <?php echo $view->translator->_('Contact') ?>: <span class="js_email"></span><br />
                    <a href="<?php echo $view->router->generate('lichess_about') ?>" target="_blank">Learn more about Lichess</a>
                </div>
                Get <a href="http://github.com/ornicar/lichess" target="_blank" title="See what's inside, fork and contribute">source code</a> or give <a class="lichess_uservoice" title="Having a suggestion, feature request or bug report? Let me know">feedback</a><br />
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
