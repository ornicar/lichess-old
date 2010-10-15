<ul class="forum_posts_list">
    <?php foreach ($posts as $post): ?>
    <li id="<?php echo $post->getNumber() ?>">
        <div class="metas">
            <div class="date">
                <span class="createdAt"><?php echo $view['time']->ago($post->getCreatedAt()) ?></span>
            </div>
        </div>
        <div class="content">
            <?php echo nl2br($view['lichess']->escape($post->getMessage())) ?>
        </div>
    </li>
    <?php endforeach ?>
</ul>
