<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Lichess\ChartBundle\Chart\PlayerMoveTimeDistributionChart;
use Lichess\ChartBundle\Chart\PlayerMoveTimeChart;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Bundle\LichessBundle\CachePagerAdapter;

class GameController extends Controller
{
    public function listCurrentAction()
    {
        return $this->render('LichessBundle:Game:listCurrent.html.twig', array(
            'ids'         => $this->get('lichess.repository.game')->findRecentStartedGameIds(9),
            'nbGames'     => $this->get('lichess_cache')->getNbGames(),
            'nbMates'     => $this->get('lichess_cache')->getNbMates()
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
        $adapter = new CachePagerAdapter($this->get('lichess.repository.game')->createRecentStartedOrFinishedQuery());
        $adapter->setNbResults($this->get('lichess_cache')->getNbGames());

        return $this->render('LichessBundle:Game:listAll.html.twig', array(
            'games'    => $this->createPaginatorForAdapter($adapter),
            'nbGames'  => $this->get('lichess_cache')->getNbGames(),
            'nbMates'  => $this->get('lichess_cache')->getNbMates()
        ));
    }

    public function listCheckmateAction()
    {
        $adapter = new CachePagerAdapter($this->get('lichess.repository.game')->createRecentMateQuery());
        $adapter->setNbResults($this->get('lichess_cache')->getNbMates());

        return $this->render('LichessBundle:Game:listMates.html.twig', array(
            'games'    => $this->createPaginatorForAdapter($adapter),
            'nbGames'  => $this->get('lichess_cache')->getNbGames(),
            'nbMates'  => $this->get('lichess_cache')->getNbMates()
        ));
    }

    public function listSuspiciousAction()
    {
        return $this->render('LichessBundle:Game:listSuspicious.html.twig', array(
            'games'    => $this->createPaginatorForQuery($this->get('lichess.repository.game')->createHighestBlurQuery())
        ));
    }

    /**
     * Join a game and start it if new, or see it as a spectator
     */
    public function showAction($id, $color)
    {
        $game = $this->get('lichess.provider')->findGame($id);

        if ('HEAD' === $this->get('request')->getMethod()) {
            return new Response(sprintf('Game #%s', $id));
        }

        // game started: enter spectator mode
        if($game->getIsStarted()) {
            $player = $game->getPlayer($color);
            if($player->getIsAi()) {
                return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id, 'color' => $player->getOpponent()->getColor())));
            }

            return $this->render('LichessBundle:Player:watch.html.twig', array(
                'game'           => $game,
                'player'         => $player,
                'version'   => $this->get('lila')->gameVersion($game),
                'checkSquareKey' => $game->getCheckSquareKey(),
                'possibleMoves'  => ($player->isMyTurn() && $game->getIsPlayable()) ? 1 : null
            ));
        }

        // game NOT started: join it
        return $this->render('LichessBundle:Game:join.html.twig', array(
            'game'  => $game,
            'color' => $game->getInvited()->getColor()
        ));
    }

    /**
     * Shows some stats about the game
     */
    public function statsAction($id)
    {
        $game = $this->get('lichess.provider')->findGame($id);
        $moveTime = new PlayerMoveTimeChart(array(
            'white' => $game->getPlayer('white'),
            'black' => $game->getPlayer('black')
        ));
        $moveTimeDistribution = array(
            'white' => new PlayerMoveTimeDistributionChart($game->getPlayer('white')),
            'black' => new PlayerMoveTimeDistributionChart($game->getPlayer('black'))
        );

        return $this->render('LichessBundle:Game:stats.html.twig', compact('game', 'moveTime', 'moveTimeDistribution'));
    }

    public function joinAction($id)
    {
        $game = $this->get('lichess.provider')->findGame($id);

        if ('HEAD' === $this->get('request')->getMethod()) {
            return new Response(sprintf('Game #%s', $id));
        }

        $player = $game->getInvited();
        $messages = $this->get('lichess.joiner')->join($player);
        if (!$messages) {
            return new RedirectResponse($this->generateUrl('lichess_game', array('id' => $id)));
        }
        $this->flush();
        $this->get('lila')->join($player, $messages);

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
    }

    protected function createPaginatorForQuery($query)
    {
        return $this->createPaginatorForAdapter(new DoctrineODMMongoDBAdapter($query));
    }

    protected function createPaginatorForAdapter($adapter)
    {
        $page = $this->container->get('request')->query->get('page', 1);
        if ($page > 100) {
            throw new NotFoundHttpException(sprintf('Older games are not available (%s)', $this->get('request')->getUri()));
        }
        $games = new Pagerfanta($adapter);
        $games->setCurrentPage($page)->setMaxPerPage(10);

        return $games;
    }

    protected function flush($safe = true)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => $safe));
    }
}
