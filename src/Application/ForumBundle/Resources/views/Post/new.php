<h2 class="postNewTitle">Reply to this topic</h2>
<form action="<?php echo $view['router']->generate('forum_post_create', array('topicId' => $topic->getId())) ?>" method="post">
    <label><span class="required">Message</span><textarea id="<?php echo $form['message']->getId() ?>" name="<?php echo $form['message']->getName() ?>"></textarea></label>
    <label><span>Author</span> <input class="authorName" name="<?php echo $form['authorName']->getName() ?>" /></label>
    <input type="submit" class="submit button" value="Reply" />
</form>
