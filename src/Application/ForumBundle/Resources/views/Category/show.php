<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $category->getName()) ?>
<?php $view['slots']->set('description', $category->getDescription()) ?>
<div class="category">
    <ol class="crumbs">
        <li><a href="<?php echo $view['forum']->urlFor() ?>">Forum</a></li>
        <li><h1><?php echo $category->getName() ?></h1></li>
    </ol>
    <p class="description"><?php echo $category->getDescription() ?></p>
    <div class="topics">
        <?php $pager = $view->render('ForumBundle::pagination.php', array('pager' => $topics->getPages(), 'url' => $view['router']->generate('forum_category_show', array('slug' => $category->getSlug())))) ?>
        <div class="pagination top"><?php echo $pager ?></div>
        <a href="<?php echo $view['router']->generate('forum_topic_new', array('categorySlug' => $category->getSlug())) ?>" class="topicNew top button">Create a new topic</a>
        <?php echo $view->render('ForumBundle:Topic:list.php', array('topics' => $topics)) ?>
        <div class="pagination bottom"><?php echo $pager ?></div>
        <a href="<?php echo $view['router']->generate('forum_topic_new', array('categorySlug' => $category->getSlug())) ?>" class="topicNew bottom button">Create a new topic</a>
    </div>
</div>
