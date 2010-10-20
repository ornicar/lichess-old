<?php foreach ($posts as $post): ?>
    <entry>
        <title>#<?php echo $post->getNumber() ?></title>
        <summary><?php echo mb_substr(str_replace("\n", ' ', $view['lichess']->escape($post->getMessage())), 0, 140) ?></summary>
        <author><?php echo $view['lichess']->escape($post->getAuthor() ?: 'Anonymous') ?></author>
        <published><?php echo $post->getCreatedAt()->format('c') ?></published>
        <link rel="alternate"><?php echo $view['forum']->urlForPost($post, true) ?></link>
    </entry>
<?php endforeach ?>
