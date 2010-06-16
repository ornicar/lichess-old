<?php foreach($messages as $message): ?>
<li><em><?php echo $message[0] ?></em><?php echo htmlentities($message[1], ENT_COMPAT, 'UTF-8') ?></li>
<?php endforeach; ?>
