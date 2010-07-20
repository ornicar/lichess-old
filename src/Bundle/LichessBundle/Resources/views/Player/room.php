<div class="lichess_chat">
    <ol class="lichess_messages">
    <?php foreach($player->getGame()->getRoom()->getMessages() as $message): ?>
        <?php if('system' === $message[0]) $message[1] = $view->translator->translate($message[1]) ?>
        <li class="<?php echo $message[0] ?>"><?php echo htmlentities($message[1], ENT_COMPAT, 'UTF-8') ?></li>
    <?php endforeach; ?>
    </ol>
    <form action="" method="POST">
        <input class="lichess_say lichess_hint" value="<?php echo $view->translator->translate('Talk in chat') ?>" />
    </form>
</div>
