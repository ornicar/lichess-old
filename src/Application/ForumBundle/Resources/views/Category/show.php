<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $category->getName()) ?>
<?php $view['slots']->set('description', $category->getDescription()) ?>

<?php $pager = $view->render('ForumBundle::pagination.php', array('pager' => $topics->getPages(), 'url' => $view['forum']->urlForCategory($category))) ?>
<?php $newTopicUrl = $view['router']->generate('forum_topic_new', array('categorySlug' => $category->getSlug())) ?>

<div class="category">

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
