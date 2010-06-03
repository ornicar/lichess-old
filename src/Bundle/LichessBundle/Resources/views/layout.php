<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>lichess - dev</title>
        <?php echo $view->stylesheets ?>
    </head>
    <body>
        <header class="header">
            <h1 class="title">
                <a href="<?php echo $view->router->generate('homepage') ?>" id="logo">lichess</a>
            </h1>
        </header>
        <div class="content">
            <?php $view->slots->output('_content') ?>
        </div>
        <?php echo $view->javascripts ?>
    </body>
</html>
