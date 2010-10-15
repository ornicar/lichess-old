<?php $view->extend('ForumBundle::layout') ?>
<?php $view['slots']->set('title', 'Add a New Topic') ?>
<div class="forum forum_new_topic">
    <div class="main form">
        <h2>Create a New Topic</h2>
        <form method="post">
            <?php $view->output('ForumBundle:Topic:form', array('form' => $form)) ?>
        </form>
    </div>
    <div class="side actions">
        <h3>Actions</h3>
        <a class="cancel" href="<?php echo $view['forum']->urlForIndex() ?>">Cancel</a>
    </div>
</div>