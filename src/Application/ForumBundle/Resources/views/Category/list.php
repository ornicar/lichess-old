<ul class="forum_categories_list">
    <?php foreach ($categories as $category): ?>
    <li>
        <div class="content">
            <a class="name" href="<?php echo $view['forum']->urlForCategory($category) ?>"><?php echo $category->getName() ?></a>
        </div>
        <div class="metas">
            <span class="numTopics"><?php echo $category->getNumTopics() . ' ' . ($category->getNumTopics() > 1 ? 'topics' : 'topic' ) ?></span>
        </div>
    </li>
    <?php endforeach ?>
</ul>
