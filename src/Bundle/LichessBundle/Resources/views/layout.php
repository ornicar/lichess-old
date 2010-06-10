<?php $view->stylesheets->add('/bundle/lichess/css/reset.css') ?>
<?php $view->stylesheets->add('/bundle/lichess/css/lichess.css') ?>
<?php $view->stylesheets->add('/bundle/lichess/css/layout.css') ?>

<?php $view->javascripts->add('/bundle/lichess/js/jquery.min.js') ?>
<?php $view->javascripts->add('/bundle/lichess/js/jquery-ui-1.8.2.custom.min.js') ?>
<?php $view->javascripts->add('/bundle/lichess/js/socket.js') ?>
<?php $view->javascripts->add('/bundle/lichess/js/ctrl.js') ?>
<?php $view->javascripts->add('/bundle/lichess/js/game.js') ?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>lichess - dev</title>
        <?php echo $view->stylesheets ?>
    </head>
    <body>
        <div class="content">
            <h1 class="site_title_wrap">
                <a class="site_title" href="<?php echo $view->router->generate('lichess_homepage') ?>" id="logo">Lichess</a>
                <div class="lichess_time">{LICHESS_TIME} ms</div>
            </h1>
            <div id="lichess">
                <?php $view->slots->output('_content') ?>
            </div>
        </div>
        <footer class="footer_wrap">
        <div class="footer">
            Open Source software built with Symfony 2<br />
            <a href="http://github.com/ornicar/lichess">Lichess source code on GitHub</a>
        </div>
        </footer>
        <?php echo $view->javascripts ?>
    </body>
</html>
