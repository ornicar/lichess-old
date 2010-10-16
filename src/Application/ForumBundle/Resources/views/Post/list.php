<div class="forum_posts_list">
    <?php foreach ($posts as $index => $post): ?>
    <div class="post">
        <div class="metas">
            <span class="authorName"><?php echo $post->getAuthorName() ?></span> <span class="createdAt"><?php echo $view['time']->ago($post->getCreatedAt()) ?></span>
            <a class="anchor" id="<?php echo $post->getNumber() ?>" href="<?php echo $view['forum']->urlForPost($post) ?>">#<?php echo $post->getNumber() ?></a>
        </div>
        <div class="message">
            <?php echo nl2br($view['lichess']->escape($post->getMessage())) ?>
        </div>
    </div>
    <?php endforeach ?>
</div>
