<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $topic->getSubject()) ?>
<div class="topic">

    <ol class="crumbs">
        <li><a href="<?php echo $view['forum']->urlFor() ?>">Forum</a></li>
        <li><a href="<?php echo $view['forum']->urlForCategory($topic->getCategory()) ?>"><?php echo $topic->getCategory()->getName() ?></a></li>
        <li><?php echo $view['lichess']->escape($topic->getSubject()) ?></li>
    </ol>

    <?php $pager = $view->render('ForumBundle::pagination.php', array('pager' => $posts->getPages(), 'url' => $view['router']->generate('forum_topic_show', array('id' => $topic->getId())))) ?>
    <div class="pagination top"><?php echo $pager ?></div>
    <h1><?php echo $topic->getSubject() ?></h1>
    <?php echo $view->render('ForumBundle:Post:list.php', array('posts' => $posts)) ?>
    <div class="pagination bottom"><?php echo $pager ?></div>

    <?php if($view['forum']->getTopicNumPages($topic) == $posts->getCurrentPageNumber()): ?>
        <div class="topic_reply">
            <?php echo $view['actions']->render('ForumBundle:Post:new', array('topicId' => $topic->getId())) ?>
        </div>
    <?php endif ?>

</div>
