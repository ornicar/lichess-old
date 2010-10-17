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
            <td class="subject"><a href="<?php echo $view['forum']->urlForTopic($topic) ?>"><?php echo $topic->getSubject() ?></a></td>
            <td class="right"><?php echo $topic->getNumViews() ?></td>
            <td class="right"><?php echo $topic->getNumPosts() - 1 ?></td>
            <td><a href="<?php echo $view['forum']->urlForPost($topic->getLastPost()) ?>"><?php echo $view['time']->ago($topic->getLastPost()->getCreatedAt()) ?></a> by <?php echo $topic->getLastPost()->getAuthorName() ?: 'Anonymous' ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
