<?php if(!$player): ?>
    <div class="lichess_cemetery lichess_cemetery_<?php echo $position ?>"></div>
<?php else: ?>
    <div class="lichess_cemetery lichess_cemetery_<?php echo $position ?>">
        <ul>
        <?php foreach($player->getDeadPieces() as $piece): ?>
            <li class="lichess_piece <?php echo strtolower($piece->getClass()) ?> <?php echo $piece->getColor() ?>"></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
