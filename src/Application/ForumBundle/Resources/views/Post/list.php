<div class="forum_posts_list">
    <?php foreach ($posts as $post): ?>
    <div id="<?php echo $post->getNumber() ?>">
        <div class="metas">
            <span class="createdAt"><?php echo $view['time']->ago($post->getCreatedAt()) ?></span> by <span class="authorName"><?php echo $post->getAuthorName() ?></span>
            <a class="post_anchor" id="<?php echo $post->getNumber() ?>" href="<?php echo $view['forum']->urlForPost($post) ?>">#<?php echo $post->getNumber() ?></a>
        </div>
        <div class="content">
            <?php echo nl2br($view['lichess']->escape($post->getMessage())) ?>
        </div>
    </div>
    <?php endforeach ?>
</div>
