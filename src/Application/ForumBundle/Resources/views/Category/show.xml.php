<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title><?php echo $category->getName() ?> - Lichess Forum</title>
    <subtitle><?php echo $category->getDescription() ?></subtitle>
    <id><?php echo $categoryUrl = $view['forum']->urlForCategory($category, true) ?></id>
    <link href="<?php echo $categoryUrl ?>" rel="alternate" />
    <link href="<?php echo $view['router']->generate('lichess_homepage') ?>" />
    <updated><?php echo $category->getLastPost()->getCreatedAt()->format('c') ?></updated>
    <?php echo $view['actions']->render('ForumBundle:Topic:list', array('category' => $category)) ?>
</feed>
