<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['slots']->set('assets_pack', 'analyse') ?>
<?php $view['slots']->set('title', 'Lichess - '.$view['translator']->_('Replay and analyse')) ?>
<?php $view['slots']->set('title_suffix', ' #'.$game->getHash()) ?>

<div class="analyse clearfix">
    <div class="board_wrap">
        <div id="GameBoard"<?php 'black' === $color && print ' class="flip"' ?>></div>
        <div id="GameButtons"></div>
    </div>
    <div class="moves_wrap">
        <h1><?php echo $view['translator']->_('Replay and analyse') ?></h1>
        <div id="GameText"></div>
    </div>
</div>

<?php $view['slots']->start('goodies') ?>
<div class="lichess_goodies">
    <a class="rotate_board" href="<?php echo $view['router']->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' => 'white' === $color ? 'black' : 'white')) ?>"><?php echo $view['translator']->_('Flip board') ?></a><br />
    <br />
    <?php foreach(array('white', 'black') as $_color): ?>
        <div>
            <?php echo $view['translator']->_(ucfirst($_color)) ?> -
            <?php if($game->getPlayer($_color)->getIsAi()): ?>
            <?php echo $view['translator']->_('%ai_name% level %ai_level%', array('%ai_name%' => 'Crafty A.I.', '%ai_level%' => $game->getPlayer($_color)->getAiLevel())) ?>
            <?php else: ?>
            <?php echo $view['translator']->_('Human') ?>
            <?php endif ?>
        </div>
    <?php endforeach ?>
    <br />
    Variant - <?php echo ucfirst($game->getVariantName()) ?><br />
    Clock - <?php echo $game->hasClock() ? $game->getClock()->getName() : 'No clock' ?><br />
    <br />
    <?php echo $view['translator']->_('Export PGN') ?>:
    <textarea id="pgnText" readonly="readonly"><?php echo $pgn ?></textarea>
</div>
<?php $view['slots']->stop() ?>
