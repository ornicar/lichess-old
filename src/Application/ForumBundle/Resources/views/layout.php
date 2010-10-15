<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?php $view['slots']->output('title', 'Forum') ?></title>
        <?php $view['stylesheets']->add('bundles/forum/css/forum.css') ?>
        <?php echo $view['stylesheets']->render() ?>
        <?php echo $view['javascripts']->render() ?>
    </head>
    <body>
        <div id="header">
            <h1 id="site-identity" class="container">
                <span class="name">Lichess forum</span>
            </h1>
        </div>
        <div id="content" class="container">
            <?php $view['slots']->output('_content') ?>
        </div>
    </body>
</html>
