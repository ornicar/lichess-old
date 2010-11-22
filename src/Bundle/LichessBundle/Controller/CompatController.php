<?php

namespace Bundle\LichessBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompatController extends Controller
{
    public function gameShowAction($id)
    {
        $newId = $id . '00';

        if($this->get('lichess.repository.game')->existsById($newId)) {
            return $this->redirect($this->generateUrl('lichess_game', array('id' => $newId)));
        }

        throw new NotFoundHttpException(sprintf('Compatibility for game "%s" not found', $id));
    }

    public function analyzeAction($id)
    {
        $newId = $id . '00';

        if($this->get('lichess.repository.game')->existsById($newId)) {
            return $this->redirect($this->generateUrl('lichess_pgn_viewer', array('id' => $newId)));
        }

        throw new NotFoundHttpException(sprintf('Compatibility for game "%s" not found', $id));
    }

    public function playerShowAction($id)
    {
        $gameId = substr($id, 0, 6);
        $playerId = substr($id, 6, 4);
        $newGameId = $gameId . '00';

        if($game = $this->get('lichess.repository.game')->find($newGameId)) {
            if($player = $game->getPlayerById($playerId)) {
                return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
            }
        }

        throw new NotFoundHttpException(sprintf('Compatibility for player "%s" not found', $id));
    }
}
