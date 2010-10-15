<ul class="forum_topics_list">
<?php foreach ($topics as $topic): ?>
    <li class="topic">
        <div class="content">
            <a class="subject" href="<?php echo $view['forum']->urlForTopic($topic) ?>"><?php echo $topic->getSubject() ?></a>
        </div>
        <div class="metas">
            <span class="creation">Created <span class="createdAt"><?php echo $view['time']->ago($topic->getCreatedAt()) ?></span>
            | <span class="numPosts"><?php echo $topic->getNumPosts() . ' ' . ($topic->getNumPosts() > 1 ? 'posts' : 'post') ?></span>
            | <a class="category" href="<?php echo $view['forum']->urlForCategory($topic->getCategory()) ?>"><?php echo $topic->getCategory()->getName() ?></a>
        </div>
    </li>
<?php endforeach ?>
</ul>
