<?php

namespace Bundle\LichessBundle;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LilaException extends \Exception {
}

class Lila
{
    protected $urlGenerator;
    private $url;

    public function __construct(UrlGeneratorInterface $urlGenerator, $url)
    {
        $this->urlGenerator = $urlGenerator;
        $this->url = $url;
    }

    public function getActivity(Player $player)
    {
        return $this->get('activity/' . $this->gameColorUrl($player));
    }

    public function nbPlayers()
    {
        return $this->get('nb-players');
    }

    public function draw(Player $player, $message)
    {
        $this->post('draw/' . $this->gameColorUrl($player), array(
            "messages" => $message
        ));
    }

    public function drawAccept(Player $player, $message)
    {
        $this->post('draw-accept/' . $this->gameColorUrl($player), array(
            "messages" => $message
        ));
    }

    public function updateVersions(Game $game)
    {
        $this->post('update-version/' . $game->getId());
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

    public function offerRematch(Game $game)
    {
        $this->reloadTable($game);
    }

    public function acceptRematch(Player $player, Game $nextGame)
    {
        // tell players to move to next game
        $this->post('accept-rematch/' . $this->gameColorUrl($player) . '/' . $nextGame->getId(), array(
            "whiteRedirect" => $this->url('lichess_player', array('id' => $nextGame->getPlayer('black')->getFullId())),
            "blackRedirect" => $this->url('lichess_player', array('id' => $nextGame->getPlayer('white')->getFullId()))
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
            "messages" => $this->encodeMessages($messages)
        ));
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
        return $this->execute($this->init($path));
    }

    private function post($path, array $data = array())
    {
        $ch = $this->init($path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

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
        curl_setopt($ch, CURLOPT_USERAGENT, 'lichess/internal');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        return $ch;
    }

    private function execute($ch)
    {
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = 'Lila error ' . curl_errno($ch) . ': ' . curl_error($ch);
            curl_close($ch);
            throw new LilaException($message);
        }

        curl_close($ch);

        return $response;
    }
}
