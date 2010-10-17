<?php $view->extend('ForumBundle::layout.php') ?>
<div class="forum forum_index">
    <h1>Lichess Forum</h1>
    <p class="forum_baseline">Don't register. Talk Chess.</p>
    <?php echo $view['actions']->render('ForumBundle:Category:list') ?>
</div>
