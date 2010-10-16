<table class="categories">
    <thead>
        <tr>
            <th><h1>Lichess Forum</h1></th>
            <th class="right">Topics</th>
            <th class="right">Posts</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $index => $category): ?>
        <tr class="<?php echo $index%2 ? 'odd' : 'even' ?>">
            <td class="subject">
                <a href="<?php echo $view['forum']->urlForCategory($category) ?>"><?php echo $category->getName() ?></a>
                <h2 class="description"><?php echo $category->getDescription() ?></h2>
            </td>
            <td class="right"><?php echo $category->getNumTopics() ?></td>
            <td class="right"><?php echo $category->getNumPosts() ?></td>
            <td><a href="<?php echo $view['forum']->urlForPost($category->getLastPost()) ?>"><?php echo $view['time']->ago($category->getLastPost()->getCreatedAt()) ?></a> by <?php echo $category->getLastPost()->getAuthorName() ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
