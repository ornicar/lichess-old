<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Search in forum') ?>
<div class="forum forum_search">
    <ul class="crumbs">
        <li>Forum</li>
    </ul>
    <form class="clearfix" action="<?php echo $view['router']->generate('forum_search') ?>" method="get">
        <label for="<?php echo $form['query']->getId() ?>">Query</label>
        #todo write me
        <div>
            <button type="submit" value="Search">Search</button>
        </div>
    </form>
</div>
