<?php $view->extend('ForumBundle::layout.php') ?>
<div class="forum forum_index">
    <h1>Lichess Forum</h1>
    <?php echo $view['actions']->render('ForumBundle:Category:list') ?>
</div>
