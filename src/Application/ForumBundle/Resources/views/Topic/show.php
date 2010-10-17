<?php $view->extend('ForumBundle::layout.php') ?>
<?php $view['slots']->set('title', $topic->getSubject()) ?>
<?php $view['slots']->set('description', $topic->getSubject().' - '.$topic->getCategory()->getDescription()) ?>

<?php $pager = $view->render('ForumBundle::pagination.php', array('pager' => $posts->getPages(), 'url' => $view['forum']->urlForTopic($topic))) ?>
<?php $replyUrl = $view['forum']->urlForTopicReply($topic) ?>
<?php $isLastPage = $view['forum']->getTopicNumPages($topic) == $posts->getCurrentPageNumber() ?>

<div class="topic">

    <ol class="crumbs">
        <li><a href="<?php echo $view['forum']->urlFor() ?>">Forum</a></li>
        <li><a href="<?php echo $view['forum']->urlForCategory($topic->getCategory()) ?>"><?php echo $topic->getCategory()->getName() ?></a></li>
        <li><h1><?php echo $view['lichess']->escape($topic->getSubject()) ?></h1></li>
    </ol>

    <div class="bar top clearfix">
        <div class="pagination"><?php echo $pager ?></div>
        <a href="<?php echo $replyUrl ?>" class="action button">Reply to this topic</a>
    </div>

    <?php echo $view->render('ForumBundle:Post:list.php', array('posts' => $posts)) ?>

    <?php if($isLastPage): ?>
        <div class="topicReply">
            <?php echo $view['actions']->render('ForumBundle:Post:new', array('topicId' => $topic->getId())) ?>
        </div>
    <?php endif ?>

    <div class="bar bottom clearfix">
        <div class="pagination"><?php echo $pager ?></div>
        <?php if(!$isLastPage): ?>
            <a href="<?php echo $replyUrl ?>" class="action button">Reply to this topic</a>
        <?php endif ?>
    </div>

</div>
