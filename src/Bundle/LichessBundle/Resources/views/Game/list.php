<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Currently playing games') ?>
<?php $view['slots']->set('assets_pack', 'gamelist') ?>

<h1 class="title">Games beeing played right now</h1>
<div class="game_list" data-url="<?php echo $view['router']->generate('lichess_games_inner', array('hashes' => $hashes)) ?>">
<?php echo $view['actions']->render('LichessBundle:Game:listInner', array('hashes' => $hashes)) ?>
</div>
