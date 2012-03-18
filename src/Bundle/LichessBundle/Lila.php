<?php

namespace Bundle\LichessBundle;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;

class Lila
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function updateVersions(Game $game)
    {
        $this->post('update-version/' . $game->getId());
    }

    public function endGame(Game $game)
    {
        $this->post('end-game/' . $game->getId());
    }

    public function talk(Game $game, $message)
    {
        $this->post('talk/' . $game->getId(), $message);
    }

    public function join(Player $player, $redirect, array $messages)
    {
        $this->post('join/' . $player->getFullId(), array(
            "redirect" => $redirect,
            "messages" => implode('$', $messages)
        ));
    }

    private function get($path)
    {
        $ch = $this->init($path);

        return execute($ch);
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
            throw new \Exception('cURL error ' . curl_errno($ch) . ': ' . curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }
}
