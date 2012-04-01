<?php

namespace Bundle\LichessBundle;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Lichess\OpeningBundle\Document\Hook;
use Lichess\OpeningBundle\Document\Entry;

class LilaException extends \Exception {
}

class Lila
{
    protected $urlGenerator;
    private $url;

    public $debug = false;

    public function __construct(UrlGeneratorInterface $urlGenerator, $url)
    {
        $this->urlGenerator = $urlGenerator;
        $this->url = $url;
    }

    public function show(Player $player)
    {
        return $this->get('show/' . $player->getFullId());
    }

    public function playerVersion(Player $player)
    {
        return $this->get('player-version/' . $this->gameColorUrl($player));
    }

    // int auth 0 or 1
    public function lobbyPreload($auth, $id = null, $canSeeChat = false)
    {
        $auth = $auth ? 1 : 0;
        $path = $id ? 'lobby/preload/' . $id : 'lobby/preload';
        $messageId = $canSeeChat ? 0 : -1;

        return $this->get($path . '?auth=' . $auth . '&messageId=' . $messageId);
    }

    public function lobbyCreate($hookOwnerId)
    {
        $this->post('lobby/create/' . $hookOwnerId);
    }

    public function lobbyAlive($hookOwnerId)
    {
        $this->post('lobby/alive/' . $hookOwnerId);
    }

    public function lobbyJoin(Player $player, array $messages)
    {
        $this->post('lobby/join/' . $this->gameColorUrl($player), array(
            "entry" => $this->encodeLobbyEntry($player->getGame()),
            "messages" => $this->encodeMessages($messages)
        ));
    }

    public function lobbyMessage()
    {
        $this->post('lobby/message');
    }

    public function getActivity(Player $player)
    {
        return (int) $this->get('activity/' . $this->gameColorUrl($player));
    }

    public function nbPlayers()
    {
        return $this->get('nb-players');
    }

    // used by the GameFixCommand
    public function outoftime(Player $player)
    {
        $this->post('outoftime/' . $player->getFullId());
    }

    public function start(Game $game)
    {
        $this->post('start/' . $game->getId(), array(
            "entry" => $this->encodeLobbyEntry($game)
        ));
    }

    public function reloadTable(Game $game)
    {
        $this->post('reload-table/' . $game->getId());
    }

    public function alive(Player $player)
    {
        $this->post('alive/' . $this->gameColorUrl($player));
    }

    public function end(Game $game)
    {
        $this->post('end/' . $game->getId(), array(
            "messages" => $game->getStatusMessage()
        ));
    }

    public function rematchOffer(Game $game)
    {
        $this->reloadTable($game);
    }

    public function rematchAccept(Player $player, Game $nextGame, array $messages)
    {
        // tell players to move to next game
        $this->post('rematch-accept/' . $this->gameColorUrl($player) . '/' . $nextGame->getId(), array(
            "whiteRedirect" => $this->url('lichess_player', array('id' => $nextGame->getPlayer('black')->getFullId())),
            "blackRedirect" => $this->url('lichess_player', array('id' => $nextGame->getPlayer('white')->getFullId())),
            "entry" => $this->encodeLobbyEntry($nextGame),
            "messages" => $this->encodeMessages($messages)
        ));
    }

    public function talk(Game $game, $message)
    {
        $this->post('talk/' . $game->getId(), $message);
    }

    public function join(Player $player, array $messages)
    {
        $this->post('join/' . $player->getFullId(), array(
            "redirect" => $this->url('lichess_player', array('id' => $player->getOpponent()->getFullId())),
            "messages" => $this->encodeMessages($messages),
            "entry" => $this->encodeLobbyEntry($player->getGame())
        ));
    }

    private function encodeLobbyEntry(Game $game)
    {
        $entry = array();
        foreach ($game->getPlayers() as $player) {
            $entry[$player->getColor() . "Username"] = $player->hasUser() ? $player->getUsername() : null;
            $entry[$player->getColor() . "UsernameElo"] = $player->getUsernameWithElo();
        }
        return implode("$", $entry);
    }

    private function gameColorUrl(Player $player)
    {
        return $player->getGame()->getId() . '/' . $player->getColor();
    }

    private function encodeMessages(array $messages)
    {
        return implode('$', $messages);
    }

    private function url($route, array $params = array())
    {
        return $this->urlGenerator->generate($route, $params);
    }

    private function get($path)
    {
        if ($this->debug) print "GET " . $path;

        return $this->execute($this->init($path));
    }

    private function post($path, array $data = array())
    {
        $ch = $this->init($path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->debug) print "POST " . $path . " " . json_encode($data);

        return $this->execute($ch);
    }

    private function init($path)
    {
        $fullPath = $this->url . '/' . trim($path, '/');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullPath);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 9);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_USERAGENT, 'lichess/api');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
        } else {
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
        }

        return $ch;
    }

    private function execute($ch)
    {
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = 'Lila error ' . curl_errno($ch) . ': ' . curl_error($ch) . "\n";
            curl_close($ch);
            throw new LilaException($message);
        }
        elseif ($this->debug) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            echo '<pre>';
            print $response . "\n";
            var_dump($info);
            if ($info['http_code'] !== 200) die;
        } else {
            curl_close($ch);
        }

        return $response;
    }
}
