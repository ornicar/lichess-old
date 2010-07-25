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
