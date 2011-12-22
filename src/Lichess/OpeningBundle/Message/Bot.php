<?php

namespace Lichess\OpeningBundle\Message;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Translation\TranslatorInterface;
use Bundle\LichessBundle\Document\Player;

class Bot
{
    public function __construct(UrlGeneratorInterface $urlGenerator, Messenger $messenger, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->messenger = $messenger;
        $this->translator = $translator;
    }

    public function onStart(Game $game)
    {
        if (!$game->hasUser()) {
            return;
        }

        $escape = function($string) {
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        };

        // php...
        $urlGenerator = $this->urlGenerator;
        $linkPlayer = function(Player $player) use ($escape, $urlGenerator)
        {
            if(!$user = $player->getUser()) { return $escape($player->getUsernameWithElo()); }
            $url = $urlGenerator->generate('fos_user_user_show', array('username' => $user->getUsername()));

            return sprintf('<a class="user_link%s" href="%s">%s</a>',
              $user->getIsOnline() ? ' online' : '', $url, $user->getUsernameWithElo()
            );
        };
        $gameUrl = $this->urlGenerator->generate('lichess_game', array('id' => $game->getId()));
        $opponents = implode(" vs ", array_map(function($player) use ($linkPlayer) {
            return $linkPlayer($player);
        }, $game->getPlayers()->toArray()));

        return $this->messenger->send("[bot]", sprintf(
            '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
            sprintf('<a class="watch" href="%s"></a>', $gameUrl),
            $opponents,
            ucfirst($this->translator->trans($game->getVariantName())),
            $this->translator->trans($game->getIsRated() ? "Rated" : "Casual"),
            $game->hasClock() ? sprintf('%d + %d', $game->getClock()->getLimitInMinutes(), $game->getClock()->getIncrement()) : $this->translator->trans("Unlimited")
        ));
    }
}
