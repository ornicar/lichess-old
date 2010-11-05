<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('title', 'Currently playing games') ?>
<?php $view['slots']->set('assets_pack', 'gamelist') ?>

<h1 class="title"><?php echo $view['translator']->_('Games being played right now') ?></h1>
<a class="all_games" href="<?php echo $view['router']->generate('lichess_list_all') ?>"><?php echo $view['translator']->_('View all %nb% games', array('%nb%' => number_format($nbGames))) ?></a>
<a class="all_games" href="<?php echo $view['router']->generate('lichess_list_mates') ?>"><?php echo $view['translator']->_('View %nb% checkmates', array('%nb%' => number_format($nbMates))) ?></a>
<div class="game_list" data-url="<?php echo $view['router']->generate('lichess_games_inner', array('ids' => $ids)) ?>">
<?php echo $view['actions']->render('LichessBundle:Game:listInner', array('ids' => $ids)) ?>
</div>
