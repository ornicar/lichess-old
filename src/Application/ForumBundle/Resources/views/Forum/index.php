<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Lichess forum') ?>
<div class="forum forum_index">
    <ul class="crumbs">
        <li>Forum</li>
    </ul>
    <div class="categories">
        <?php echo $view['actions']->render('ForumBundle:Category:list') ?>
    </div>
</div>
