<form action="<?php echo $view['router']->generate('forum_post_create', array('topicId' => $topic->getId())) ?>" method="post">
    <textarea id="<?php echo $form['message']->getId() ?>" name="<?php echo $form['message']->getName() ?>"></textarea>
    <input type="submit" value="Reply" />
</form>
