<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GameController extends Controller
{
    public function listCurrentAction()
    {
        return $this->render('LichessBundle:Game:listCurrent.html.twig', array(
            'ids'         => $this->get('lichess.repository.game')->findRecentStartedGameIds(9),
            'nbGames'     => $this->get('lichess.repository.game')->getNbGames(),
            'nbMates'     => $this->get('lichess.repository.game')->getNbMates()
        ));
    }

    public function listCurrentInnerAction($ids)
    {
        return $this->render('LichessBundle:Game:listCurrentInner.html.twig', array(
            'games' => $this->get('lichess.repository.game')->findGamesByIds($ids)
        ));
    }

    public function listAllAction()
    {
        return $this->render('LichessBundle:Game:listAll.html.twig', array(
            'games'    => $this->createPaginatorForQuery($this->get('lichess.repository.game')->createRecentStartedOrFinishedQuery()),
            'nbGames'  => $this->get('lichess.repository.game')->getNbGames(),
            'nbMates'  => $this->get('lichess.repository.game')->getNbMates(),
            'pagerUrl' => $this->generateUrl('lichess_list_all')
        ));
    }

    public function listCheckmateAction()
    {
        return $this->render('LichessBundle:Game:listMates.html.twig', array(
            'games'    => $this->createPaginatorForQuery($this->get('lichess.repository.game')->createRecentMateQuery()),
            'nbGames'  => $this->get('lichess.repository.game')->getNbGames(),
            'nbMates'  => $this->get('lichess.repository.game')->getNbMates(),
            'pagerUrl' => $this->generateUrl('lichess_list_mates')
        ));
    }

    /**
     * Join a game and start it if new, or see it as a spectator
     */
    public function showAction($id, $color)
    {
        $game = $this->get('lichess.provider')->findGame($id);

        // game started: enter spectator mode
        if($game->getIsStarted()) {
            $player = $game->getPlayer($color);
            if($player->getIsAi()) {
                return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id, 'color' => $player->getOpponent()->getColor())));
            }

            return $this->render('LichessBundle:Player:watch.html.twig', array(
                'game'           => $game,
                'player'         => $player,
                'checkSquareKey' => $this->get('lichess.analyser_factory')->create($game->getBoard())->getCheckSquareKey($game->getTurnPlayer()),
                'possibleMoves'  => ($player->isMyTurn() && $game->getIsPlayable()) ? 1 : null
            ));
        }

        // game NOT started: join it
        return $this->render('LichessBundle:Game:join.html.twig', array(
            'game'  => $game,
            'color' => $game->getInvited()->getColor()
        ));
    }

    public function showHeadAction($id, $color)
    {
        $game = $this->get('lichess.provider')->findGame($id);

        return new Response(sprintf('Game #%s', $id));
    }

    public function joinAction($id)
    {
        $game = $this->get('lichess.provider')->findGame($id);

        try {
            $this->get('lichess.joiner')->join($game);
        } catch (InvalidArgumentException $e) {
            return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id)));
        }
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $game->getInvited()->getFullId())));
    }

    public function inviteFriendAction()
    {
        $form = $this->get('lichess.form.manager')->createFriendForm();
        $form->bind($this->get('request'), $form->getData());

        if($form->isValid()) {
            $player = $this->get('lichess.starter.friend')->start($form->getData());
            $this->flush();
            return new RedirectResponse($this->generateUrl('lichess_wait_friend', array('id' => $player->getFullId())));
        }

        return $this->render('LichessBundle:Game:inviteFriend.html.twig', array('form' => $form));
    }

    public function inviteAiAction()
    {
        $form = $this->get('lichess.form.manager')->createAiForm();
        $form->bind($this->get('request'), $form->getData());

        if($form->isValid()) {
            $player = $this->get('lichess.starter.ai')->start($form->getData());
            $this->flush();
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
        }

        return $this->render('LichessBundle:Game:inviteAi.html.twig', array('form' => $form));
    }

    public function inviteAnybodyAction()
    {
        $form = $this->get('lichess.form.manager')->createAnybodyForm();
        $form->bind($this->get('request'), $form->getData());

        if($form->isValid()) {
            $return = $this->get('lichess.starter.anybody')->start($form->getData());
            $this->flush();
            if ($return instanceof Game) {
                return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $return->getId())));
            } elseif ($return instanceof Player) {
                return new RedirectResponse($this->generateUrl('lichess_wait_anybody', array('id' => $return->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAnybody.html.twig', array('form' => $form));
    }

    protected function createPaginatorForQuery($query)
    {
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        return $games;
    }

    protected function flush($safe = true)
    {
        return $this->get('lichess.object_manager')->flush(array('safe' => $safe));
    }
}
