<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $category->getName()) ?>
<?php $view['slots']->set('description', $category->getDescription()) ?>
<?php $view['slots']->set('feed_link', '<link href="'.$view['router']->generate('forum_category_show', array('slug' => $category->getSlug(), '_format' => 'xml')).'" type="application/atom+xml" rel="alternate" title="'.$category->getName().' - Lichess Forum" />') ?>

<div class="category">

    <a href="<?php echo $view['router']->generate('forum_category_show', array('slug' => $category->getSlug(), '_format' => 'xml')) ?>" title="<?php echo $view['translator']->_('Follow this category') ?>" class="forum_feed_link"></a>

    <ol class="crumbs">
        <li><a href="<?php echo $view['forum']->urlFor() ?>">Forum</a></li>
        <li><h1><?php echo $category->getName() ?></h1></li>
    </ol>

    <p class="description"><?php echo $category->getDescription() ?></p>

    <?php echo $view['actions']->render('ForumBundle:Topic:list', array('category' => $category), array('query' => array('page' => $page))) ?>

</div>
