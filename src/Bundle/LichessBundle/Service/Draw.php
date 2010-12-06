<?php

namespace Bundle\LichessBundle\Service;

use Bundle\LichessBundle\Model;

class Draw extends Service
{
    public function offer($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if(!$game->getIsFinished()) {
            if(!$player->getIsOfferingDraw()) {
                if($player->getOpponent()->getIsOfferingDraw()) {
                    return false;
                }
                $this->container->get('lichess.messenger')->addSystemMessage($game, 'Draw offer sent');
                $player->setIsOfferingDraw(true);
                $player->getOpponent()->addEventToStack(array('type' => 'reload_table'));
                $this->container->get('lichess.object_manager')->flush();
                $this->container->get('logger')->notice(sprintf('Player:offerDraw game:%s', $game->getId()));
            } else {
                $this->container->get('logger')->warn(sprintf('Player:offerDraw already offered game:%s', $game->getId()));
            }
        } else {
            $this->container->get('logger')->warn(sprintf('Player:offerDraw on finished game:%s', $game->getId()));
        }

        return true;
    }

    public function declineOffer($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $this->container->get('lichess.messenger')->addSystemMessage($game, 'Draw offer declined');
            $player->getOpponent()->setIsOfferingDraw(false);
            $player->getOpponent()->addEventToStack(array('type' => 'reload_table'));
            $this->container->get('lichess.object_manager')->flush();
            $this->container->get('logger')->notice(sprintf('Player:declineDrawOffer game:%s', $game->getId()));
        } else {
            $this->container->get('logger')->warn(sprintf('Player:declineDrawOffer no offered draw game:%s', $game->getId()));
        }
    }

    public function acceptOffer($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $this->container->get('lichess.messenger')->addSystemMessage($game, 'Draw offer accepted');
            $game->setStatus(Model\Game::DRAW);
            $this->container->get('lichess_finisher')->finish($game);
            $player->addEventToStack(array('type' => 'end'));
            $player->getOpponent()->addEventToStack(array('type' => 'end'));
            $this->container->get('lichess.object_manager')->flush();
            $this->container->get('logger')->notice(sprintf('Player:acceptDrawOffer game:%s', $game->getId()));
        } else {
            $this->container->get('logger')->warn(sprintf('Player:acceptDrawOffer no offered draw game:%s', $game->getId()));
        }
    }

    public function cancelOffer($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if($player->getIsOfferingDraw()) {
            $this->container->get('lichess.messenger')->addSystemMessage($game, 'Draw offer canceled');
            $player->setIsOfferingDraw(false);
            $player->getOpponent()->addEventToStack(array('type' => 'reload_table'));
            $this->container->get('lichess.object_manager')->flush();
            $this->container->get('logger')->notice(sprintf('Player:cancelDrawOffer game:%s', $game->getId()));
        } else {
            $this->container->get('logger')->warn(sprintf('Player:cancelDrawOffer no offered draw game:%s', $game->getId()));
        }
    }

    public function claimOffer($id)
    {
        $player = $this->findPlayer($id);
        $game = $player->getGame();
        if(!$game->getIsFinished() && $game->isThreefoldRepetition() && $player->isMyTurn()) {
            $game->setStatus(Model\Game::DRAW);
            $this->container->get('lichess_finisher')->finish($game);
            $player->addEventToStack(array('type' => 'end'));
            $player->getOpponent()->addEventToStack(array('type' => 'end'));
            $this->container->get('lichess.object_manager')->flush();
            $this->container->get('logger')->notice(sprintf('Player:claimDraw game:%s', $game->getId()));
        }
        else {
            $this->container->get('logger')->warn(sprintf('Player:claimDraw FAIL game:%s', $game->getId()));
        }
    }
}