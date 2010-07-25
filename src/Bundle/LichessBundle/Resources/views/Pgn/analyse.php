<?php $view->extend('LichessBundle::layout') ?>
<?php $view->slots->set('assets_pack', 'analyse') ?>
<?php $view->slots->set('title', 'Lichess - '.$view->translator->_('Replay and analysis')) ?>

<textarea id="pgnText"><?php echo $pgn ?></textarea>
<div class="analyse clearfix">
    <div class="board_wrap">
        <div id="GameBoard"></div> 
        <div id="GameButtons"></div> 
    </div>
    <div class="moves_wrap">
        <h1><?php echo $view->translator->_('Replay and analysis') ?></h1>
        <div id="GameText"></div>
    </div>
</div>

<?php $view->slots->start('goodies') ?>
<div class="lichess_goodies">
    <?php foreach(array('white', 'black') as $color): ?>
        <div>
            <?php echo $view->translator->_(ucfirst($color)) ?> - 
            <?php if($game->getPlayer($color)->getIsAi()): ?>
            <?php echo $view->translator->_('%ai_name% level %ai_level%', array('%ai_name%' => 'Crafty A.I.', '%ai_level%' => $game->getPlayer($color)->getAiLevel())) ?>
            <?php else: ?>
            <?php echo $view->translator->_('Human') ?> 
            <?php endif ?>
        </div>
    <?php endforeach ?>
    <div class="export_link">
        <a href="<?php echo $view->router->generate('lichess_pgn_export', array('hash' => $game->getHash())) ?>">Export PGN</a>
    </div>
</div>
<?php $view->slots->stop() ?>
