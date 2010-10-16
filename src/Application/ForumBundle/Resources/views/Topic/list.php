<table class="forum_topics_list">
    <thead>
        <tr>
            <th></th>
            <th>Views</th>
            <th>Posts</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($topics as $topic): ?>
        <tr>
            <td><a href="<?php echo $view['forum']->urlForTopic($topic) ?>"><?php echo $topic->getSubject() ?></a></td>
            <td><?php echo $topic->getNumViews() ?></td>
            <td><?php echo $topic->getNumPosts() ?></td>
            <td><?php echo $view['time']->ago($topic->getLastPost()->getCreatedAt()) ?> by <?php echo $topic->getLastPost()->getAuthorName() ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
