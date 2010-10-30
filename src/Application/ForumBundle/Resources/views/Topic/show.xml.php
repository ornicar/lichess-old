<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title><?php echo $view['lichess']->escape($topic->getSubject()) ?> - Lichess Forum</title>
    <id><?php echo $topicUrl = $view['forum']->urlForTopic($topic, true) ?></id>
    <link href="<?php echo $topicUrl ?>" rel="alternate" />
    <link href="<?php echo $view['router']->generate('lichess_homepage') ?>" />
    <updated><?php echo $topic->getPulledAt()->format('c') ?></updated>
    <author>
        <name><?php echo $view['lichess']->escape($topic->getFirstPost()->getAuthorName()) ?></name>
    </author>
    <?php echo $view->render('ForumBundle:Post:list.php', array('posts' => $posts)) ?>
</feed>
