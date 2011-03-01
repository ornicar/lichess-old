<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Chess\Analyser;
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
        $game = $this->findGame($id);

        // game started: enter spectator mode
        if($game->getIsStarted()) {
            $player = $game->getPlayer($color);
            if($player->getIsAi()) {
                return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id, 'color' => $player->getOpponent()->getColor())));
            }
            $analyser = new Analyser($game->getBoard());

            return $this->render('LichessBundle:Player:watch.html.twig', array(
                'game'           => $game,
                'player'         => $player,
                'checkSquareKey' => $analyser->getCheckSquareKey($game->getTurnPlayer()),
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
        $game = $this->findGame($id);

        return new Response(sprintf('Game #%s', $id));
    }

    public function joinAction($id)
    {
        $game = $this->findGame($id);

        if($game->getIsStarted()) {
            $this->get('lichess.logger')->warn($game, 'Game:join started');
            return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id)));
        }

        $this->get('lichess.blamer.player')->blame($game->getInvited());
        $game->start();
        $game->getCreator()->addEventToStack(array(
            'type' => 'redirect',
            'url'  => $this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId()))
        ));
        $this->get('lichess.object_manager')->flush(array('safe' => true));
        $this->get('lichess.logger')->notice($game, 'Game:join');

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $game->getInvited()->getFullId())));
    }

    public function inviteFriendAction($color)
    {
        $form = $this->get('lichess.form.manager')->createFriendForm();
        $form->bind($this->get('request'), $form->getData());

        if($form->isValid()) {
            $player = $this->get('lichess.starter.friend')->start($form->getData(), $color);
            return new RedirectResponse($this->generateUrl('lichess_wait_friend', array('id' => $player->getFullId())));
        }

        return $this->render('LichessBundle:Game:inviteFriend.html.twig', compact('form', 'color'));
    }

    public function inviteAiAction($color)
    {
        $form = $this->get('lichess.form.manager')->createAiForm();
        $form->bind($this->get('request'), $form->getData());

        if($form->isValid()) {
            $player = $this->get('lichess.starter.ai')->start($form->getData(), $color);
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
        }

        return $this->render('LichessBundle:Game:inviteAi.html.twig', compact('form', 'color'));
    }

    public function inviteAnybodyAction($color)
    {
        $form = $this->get('lichess.form.manager')->createAnybodyForm();
        $form->bind($this->get('request'), $form->getData());

        if($form->isValid()) {
            $return = $this->get('lichess.starter.anybody')->start($form->getData(), $color);
            if ($return instanceof Game) {
                return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $return->getId())));
            } elseif ($return instanceof Player) {
                return new RedirectResponse($this->generateUrl('lichess_wait_anybody', array('id' => $return->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAnybody.html.twig', compact('form', 'color'));
    }

    /**
     * Return the game for this id
     *
     * @param string $id
     * @return Game
     */
    protected function findGame($id)
    {
        $game = $this->get('lichess.repository.game')->findOneById($id);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$id);
        }

        return $game;
    }

    protected function createPaginatorForQuery($query)
    {
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        return $games;
    }
}
