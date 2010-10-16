<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $category->getName().' - New topic') ?>
<ol class="crumbs">
    <li><a href="<?php echo $view['forum']->urlFor() ?>">Forum</a></li>
    <li><a href="<?php echo $view['forum']->urlForCategory($category) ?>"><?php echo $category->getName() ?></a></li>
    <li>New topic</li>
</ol>
<br />
<h1>New topic</h1>
<form action="<?php echo $view['router']->generate('forum_topic_create', array('categorySlug' => $category->getSlug())) ?>" method="post">
    <label><span class="required">Category</span> <?php echo $form['category']->widget() ?></label>
    <label><span class="required">Subject</span> <?php echo $form['subject']->widget(array('class' => 'subject')) ?></label>
    <label><span class="required">Message</span> <?php echo $form['firstPost']['message']->widget() ?></label>
    <label><span>Author</span> <?php echo $form['firstPost']['authorName']->widget(array('class' => 'authorName')) ?></label>
    <button class="submit button" type="submit">Create the topic</button>
</form>
