<?php $view->extend('LichessBundle::layout') ?>
<?php $view->slots->set('assets_pack', 'analyse') ?>
<?php $view->slots->set('title', 'Lichess - '.$view->translator->_('Replay and analyse')) ?>

<textarea id="pgnText"><?php echo $pgn ?></textarea>
<div class="analyse clearfix">
    <div class="board_wrap">
        <div id="GameBoard"<?php 'black' === $color && print ' class="flip"' ?>></div> 
        <div id="GameButtons"></div> 
    </div>
    <div class="moves_wrap">
        <h1><?php echo $view->translator->_('Replay and analyse') ?></h1>
        <div id="GameText"></div>
    </div>
</div>

<?php $view->slots->start('goodies') ?>
<div class="lichess_goodies">
    <?php foreach(array('white', 'black') as $_color): ?>
        <div>
            <?php echo $view->translator->_(ucfirst($_color)) ?> - 
            <?php if($game->getPlayer($_color)->getIsAi()): ?>
            <?php echo $view->translator->_('%ai_name% level %ai_level%', array('%ai_name%' => 'Crafty A.I.', '%ai_level%' => $game->getPlayer($_color)->getAiLevel())) ?>
            <?php else: ?>
            <?php echo $view->translator->_('Human') ?> 
            <?php endif ?>
        </div>
    <?php endforeach ?>
    <ul class="links">
        <li><a class="rotate_board" href="<?php echo $view->router->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' => 'white' === $color ? 'black' : 'white')) ?>">
           <?php echo $view->translator->_('Flip board') ?>
        </a></li>
        <li><a href="<?php echo $view->router->generate('lichess_pgn_export', array('hash' => $game->getHash())) ?>"><?php echo $view->translator->_('Export PGN') ?></a></li>
    </ul>
</div>
<?php $view->slots->stop() ?>
