<?php $view->extend('LichessBundle::layout.php') ?>
<?php $pager = $games->getPages() ?>
<?php $view['slots']->set('assets_pack', 'gamelist') ?>

<h1 class="title"><?php echo $view['slots']->get('list_type') ?> <?php echo $pager->firstItemNumber; ?> - <?php echo $pager->lastItemNumber; ?> of <?php echo number_format($pager->totalItemCount); ?></h1>
<?php echo $view['slots']->get('list_menu') ?>
<div class="all_games">
    <div class="pager pager_top"><?php echo $pagination = $view->render('LichessBundle::pagination.php', array('pager' => $pager, 'url' => $pagerUrl)) ?></div>
    <div class="all_games_inner">
    <?php foreach($games as $game): ?>
        <div class="game_row clearfix">
            <?php echo $view->render('LichessBundle:Game:mini.php', array('game' => $game)) ?>
            <div class="infos">
                <a class="link" href="<?php echo $url = $view['router']->generate('lichess_game', array('hash' => $game->getHash()), true) ?>"><?php echo $url ?></a>
                <br /><br />
                <?php foreach($game->getPlayers() as $color => $player): ?>
                    <?php echo $view['translator']->_(ucfirst($player->getOpponent()->getColor())) ?> -
                    <?php if($player->getOpponent()->getIsAi()): ?>
                        <?php echo $view['translator']->_('%ai_name% level %ai_level%', array('%ai_name%' => 'Crafty A.I.', '%ai_level%' => $player->getOpponent()->getAiLevel())) ?>
                    <?php else: ?>
                        <?php echo $view['translator']->_('Human') ?>
                    <?php endif; ?>
                    <br />
                <?php endforeach ?>
                <br />
                <?php echo 1+floor($game->getTurns()/2) ?>.
                <?php if($game->getIsFinished()): ?>
                    <?php if($winner = $game->getWinner()): ?>
                        <?php echo $view['translator']->_($game->getStatusMessage()) ?><br /><?php echo $view['translator']->_(ucfirst($winner->getColor()).' is victorious') ?>
                    <?php else: ?>
                        <?php echo $view['translator']->_($game->getStatusMessage()) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if('white' === $player->getGame()->getTurnPlayer()->getColor()): ?>
                        <?php echo $view['translator']->_('White plays') ?>
                    <?php else: ?>
                        <?php echo $view['translator']->_('Black plays') ?>
                    <?php endif; ?>
                <?php endif; ?>
                <br /><br />
                Variant: <?php echo $game->getVariantName() ?>
                <br /><br />
                Time control: <?php echo $game->hasClock() ? ($game->getClock()->getLimit() / 60).' minutes/side' : 'no' ?>
                <br /><br />
                <a href="<?php echo $view['router']->generate('lichess_pgn_viewer', array('hash' => $game->getHash(), 'color' => $game->getCreator()->getColor())) ?>">&gt;&gt; Replay and Analyse</a>
            </div>
        </div>
    <?php endforeach ?>
    </div>
    <div class="pager pager_bottom"><?php echo $pagination ?></div>
</div>
