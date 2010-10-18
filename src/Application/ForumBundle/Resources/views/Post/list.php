<div class="forum_posts_list">
    <?php foreach ($posts as $index => $post): ?>
    <div class="post" id="<?php echo $post->getNumber() ?>">
        <div class="metas">
            <span class="authorName"><?php echo $view['lichess']->escape($post->getAuthorName()) ?: 'Anonymous' ?></span> <span class="createdAt"><?php echo $view['time']->ago($post->getCreatedAt()) ?></span>
            <a class="anchor" href="<?php echo $view['forum']->urlForPost($post) ?>">#<?php echo $post->getNumber() ?></a>
        </div>
        <div class="message">
            <?php echo nl2br($view['forum']->autoLink($view['lichess']->escape($post->getMessage()))) ?>
        </div>
    </div>
    <?php endforeach ?>
</div>
