<div class="lichess_chat">
    <ol class="lichess_messages">
    <?php foreach($player->getGame()->getRoom()->getMessages() as $message): ?>
        <li class="<?php echo $message[0] ?>"><?php echo htmlentities($message[1], ENT_COMPAT, 'UTF-8') ?></li>
    <?php endforeach; ?>
    </ol>
    <form action="<?php echo $view->router->generate('lichess_say', array('hash' => $player->getFullHash())) ?>" method="POST">
        <input class="lichess_say lichess_hint" value="Talk in chat" />
    </form>
</div>
