<?php $pager = $view->render('ForumBundle::pagination.php', array('pager' => $topics->getPages(), 'url' => $view['forum']->urlForCategory($category))) ?>
<?php $newTopicUrl = $view['router']->generate('forum_category_topic_new', array('slug' => $category->getSlug())) ?>

<div class="bar top clearfix">
    <div class="pagination"><?php echo $pager ?></div>
    <a href="<?php echo $newTopicUrl ?>" class="action button">Create a new topic</a>
</div>

<table class="forum_topics_list">
    <thead>
        <tr>
            <th>Topics</th>
            <th class="right">Views</th>
            <th class="right">Replies</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($topics as $index => $topic): ?>
        <tr class="<?php echo $index%2 ? 'odd' : 'even' ?>">
            <td class="subject"><a href="<?php echo $view['forum']->urlForTopic($topic) ?>"><?php echo $view['lichess']->escape($topic->getSubject()) ?></a></td>
            <td class="right"><?php echo $topic->getNumViews() ?></td>
            <td class="right"><?php echo $topic->getNumPosts() - 1 ?></td>
            <td><a href="<?php echo $view['forum']->urlForPost($topic->getLastPost()) ?>"><?php echo $view['time']->ago($topic->getLastPost()->getCreatedAt()) ?></a><br />by <?php echo $view['lichess']->escape($topic->getLastPost()->getAuthorName()) ?: 'Anonymous' ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div class="bar bottom clearfix">
    <div class="pagination"><?php echo $pager ?></div>
    <a href="<?php echo $newTopicUrl ?>" class="action button">Create a new topic</a>
</div>
