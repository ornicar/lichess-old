<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $category->getName()) ?>
<?php $view['slots']->set('description', $category->getDescription()) ?>
<?php $view['slots']->set('feed_link', '<link href="'.$view['router']->generate('forum_category_show', array('slug' => $category->getSlug(), '_format' => 'xml')).'" type="application/atom+xml" rel="alternate" title="'.$category->getName().' - Lichess Forum" />') ?>

<?php $pager = $view->render('ForumBundle::pagination.php', array('pager' => $topics->getPages(), 'url' => $view['forum']->urlForCategory($category))) ?>
<?php $newTopicUrl = $view['router']->generate('forum_topic_new', array('categorySlug' => $category->getSlug())) ?>

<div class="category">

    <a href="<?php echo $view['router']->generate('forum_category_show', array('slug' => $category->getSlug(), '_format' => 'xml')) ?>" title="<?php echo $view['translator']->_('Follow this category') ?>" class="forum_feed_link"></a>

    <ol class="crumbs">
        <li><a href="<?php echo $view['forum']->urlFor() ?>">Forum</a></li>
        <li><h1><?php echo $category->getName() ?></h1></li>
    </ol>

    <p class="description"><?php echo $category->getDescription() ?></p>

    <div class="bar top clearfix">
        <div class="pagination"><?php echo $pager ?></div>
        <a href="<?php echo $newTopicUrl ?>" class="action button">Create a new topic</a>
    </div>

    <?php echo $view->render('ForumBundle:Topic:list.php', array('topics' => $topics)) ?>

    <div class="bar bottom clearfix">
        <div class="pagination"><?php echo $pager ?></div>
        <a href="<?php echo $newTopicUrl ?>" class="action button">Create a new topic</a>
    </div>

</div>
