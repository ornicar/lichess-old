<div class="game_list_inner clearfix">
<?php foreach($games as $game): ?>
    <div class="game_mini"><?php echo $view->render('LichessBundle:Game:mini.php', array('game' => $game)) ?></div>
<?php endforeach ?>
</div>
