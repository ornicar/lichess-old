<?php $view->extend('ForumBundle::layout.php') ?>
<div class="forum forum_index">
    <div class="categories">
        <?php echo $view['actions']->render('ForumBundle:Category:list') ?>
    </div>
</div>
