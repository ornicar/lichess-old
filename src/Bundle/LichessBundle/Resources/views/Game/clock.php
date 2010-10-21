<?php $time = $clock->getRemainingTime($color) ?>
<div class="clock clock_<?php echo $position ?> clock_<?php echo $color ?><?php echo !$time ? ' outoftime' : '' ?>" data-time="<?php echo $time ?>">
    <?php printf('%02d:%02d', floor($time/60), $time%60) ?>
</div>
