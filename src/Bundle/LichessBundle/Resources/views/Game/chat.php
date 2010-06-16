<div class="lichess_chat">
    <ol class="lichess_messages">
    <?php foreach($room->getMessages() as $message): ?>
        <li><em><?php echo $message[0] ?></em><span><?php echo htmlentities($message[1], ENT_COMPAT, 'UTF-8') ?></span></li>
    <?php endforeach; ?>
    </ol>
</div>
