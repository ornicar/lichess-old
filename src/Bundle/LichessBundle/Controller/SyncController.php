<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class SyncController extends Controller
{
    public function syncAction($id, $color, $version, $playerFullId)
    {
        session_write_close();
        $player = $this->get('lichess.provider')->findPublicPlayer($id, $color);
        $memory = $this->get('lichess.memory');

        if($playerFullId) {
            $memory->setAlive($player);
        }
        $player->getGame()->cachePlayerVersions();

        $this->container->get('lichess.http_push')->poll($player, $version);

        return $this->renderJson($this->get('lichess.client_updater')->getEventsSinceClientVersion($player, $version, (bool) $playerFullId));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
