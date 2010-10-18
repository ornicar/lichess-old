<h2 class="postNewTitle" id="reply">Reply to this topic</h2>
<form action="<?php echo $view['router']->generate('forum_post_create', array('topicId' => $topic->getId())) ?>#reply" method="post">
    <label><span class="required">Message</span><textarea id="<?php echo $form['message']->getId() ?>" name="<?php echo $form['message']->getName() ?>"><?php echo $form->getData()->getMessage() ?></textarea></label>
    <label><span>Author</span><input class="authorName" name="<?php echo $form['authorName']->getName() ?>" value="<?php echo $form->getData()->getAuthorName() ?>"/></label>
    <input type="submit" class="submit button" value="Reply" />
</form>
