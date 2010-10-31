<?php $view->extend('LichessBundle:Game:list.php') ?>
<?php $view['slots']->set('title', $view['translator']->_('View all %nb% games', array('%nb%' => number_format($nbGames)))) ?>
<?php $view['slots']->set('list_type', 'All games') ?>
<?php $view['slots']->start('list_menu') ?>
<a class="game_list" href="<?php echo $view['router']->generate('lichess_games') ?>"><?php echo $view['translator']->_('Games being played right now') ?></a>
<a class="game_list" href="<?php echo $view['router']->generate('lichess_list_mates') ?>"><?php echo $view['translator']->_('View %nb% Checkmates', array('%nb%' => $nbMates)) ?></a>
<?php $view['slots']->stop() ?>
