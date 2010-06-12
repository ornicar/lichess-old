<div class="lichess_cemetery lichess_cemetery_<?php echo $position ?> <?php echo $player->getColor() ?>">
<?php foreach($player->getDeadPieces() as $piece): ?>
    <div class="lichess_piece <?php echo strtolower($piece->getClass()) ?> <?php echo $piece->getColor() ?>"></div>
<?php endforeach; ?>
</div>
