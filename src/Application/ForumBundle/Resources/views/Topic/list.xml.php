<?php foreach ($topics as $topic): ?>
    <entry>
        <title><?php echo $view['lichess']->escape($topic->getSubject()) ?></title>
        <author><?php echo $view['lichess']->escape($topic->getAuthor() ?: 'Anonymous') ?></author>
        <updated><?php echo $topic->getPulledAt()->format('c') ?></updated>
        <id><?php echo $topicUrl = $view['forum']->urlForTopic($topic, true) ?></id>
        <link rel="alternate"><?php echo $topicUrl ?></link>
    </entry>
<?php endforeach ?>
