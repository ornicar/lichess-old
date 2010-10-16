<table class="forum_categories_list">
    <thead>
        <tr>
            <th></th>
            <th>Topics</th>
            <th>Posts</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
        <tr>
            <td><a href="<?php echo $view['forum']->urlForCategory($category) ?>"><?php echo $category->getName() ?></a></td>
            <td><?php echo $category->getNumTopics() ?></td>
            <td><?php echo $category->getNumPosts() ?></td>
            <td><a href="<?php echo $view['forum']->urlForPost($category->getLastPost()) ?>"><?php echo $view['time']->ago($category->getLastPost()->getCreatedAt()) ?></a> by <?php echo $category->getLastPost()->getAuthorName() ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
