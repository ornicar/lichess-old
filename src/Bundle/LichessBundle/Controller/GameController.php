<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Chess\Analyser as Analyser;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function showAction($hash)
    {
        $game = $this->container->getLichessPersistenceService()->find($hash);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$hash);
        } 

        if($game->getIsStarted()) {
            $analyser = new Analyser($player->getGame()->getBoard());
            if($analyser->isKingAttacked($game->getTurnPlayer())) {
                $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
            }
            else {
                $checkSquareKey = null;
            }
            return $this->render('LichessBundle:Game:show', array('player' => $game->getCreator()));
        }

        $player = $game->getInvited();
        $game->setIsStarted(true);
        $this->container->getLichessPersistenceService()->save($game);

        return $this->redirect($this->generateUrl('lichess_player', array(
            'hash' => $player->getFullHash(),
            'checkSquareKey' => $checkSquareKey
        )));
    }
}
