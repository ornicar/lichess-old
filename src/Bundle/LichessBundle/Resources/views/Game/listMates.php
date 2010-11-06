<?php $view->extend('LichessBundle:Game:list.php') ?>
<?php $view['slots']->set('title', $view['translator']->_('View %nb% checkmates', array('%nb%' => number_format($nbMates)))) ?>
<?php $view['slots']->set('list_type', 'Checkmates') ?>
<?php $view['slots']->start('list_menu') ?>
<a class="game_list" href="<?php echo $view['router']->generate('lichess_list_current') ?>"><?php echo $view['translator']->_('Games being played right now') ?></a>
<a class="game_list" href="<?php echo $view['router']->generate('lichess_list_all') ?>"><?php echo $view['translator']->_('View all %nb% games', array('%nb%' => $nbGames)) ?></a>
<?php $view['slots']->stop() ?>
