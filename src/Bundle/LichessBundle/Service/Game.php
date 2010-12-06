<?php

namespace Bundle\LichessBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Bundle\LichessBundle\Form\GameConfig as Config;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Model;

class Game extends Service {

    public function createAiGame(Config $config, $color)
    {
        $player = $this->container->get('lichess_generator')->createGameForPlayer($color, $config->variant);
        $this->container->get('lichess.blamer.player')->blame($player);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->start();

        if($player->isBlack()) {
            $stackClass = $this->container->getParameter('lichess.model.stack.class');
            $manipulator = new Manipulator($game, new $stackClass());
            $manipulator->setContainer($this->container);

            $manipulator->play($this->container->get('lichess_ai')->move($game, $opponent->getAiLevel()));
        }

        $this->container->get('lichess.object_manager')->persist($game);
        $this->container->get('lichess.object_manager')->flush();
        
        $this->container->get('logger')->notice(sprintf('Game:inviteAi create game:%s, variant:%s', $game->getId(), $game->getVariantName()));
        $this->cachePlayerVersions($game);

        return $player;
    }

    public function createFriendGame(Config $config, $color)
    {
        $player = $this->container->get('lichess_generator')->createGameForPlayer($color, $config->variant);
        $this->container->get('lichess.blamer.player')->blame($player);
        $game = $player->getGame();
        if($config->time) {
            $clockClass = $this->container->getParameter('lichess.model.clock.class');
            $clock = new $clockClass($config->time * 60);
            $game->setClock($clock);
        }
        $this->container->get('lichess.object_manager')->persist($game);
        $this->container->get('lichess.object_manager')->flush();
        $this->container->get('logger')->notice(sprintf('Game:inviteFriend create game:%s, variant:%s, time:%d', $game->getId(), $game->getVariantName(), $config->time));
        $this->cachePlayerVersions($game);

        return $player;
    }


    public function createInvitation(Config $config, $color)
    {
        $queue = $this->container->get('lichess.seek_queue');
        $result = $queue->add($config->variants, $config->times, $this->container->get('session')->get('lichess.session_id'), $color);
        $game = $result['game'];
        if(!$game) {
            return false;
        }
        if($result['status'] === $queue::FOUND) {
            if(!$this->container->get('lichess_synchronizer')->isConnected($game->getCreator())) {
                $this->container->get('lichess.object_manager')->remove($game);
                $this->container->get('lichess.object_manager')->flush();
                $this->container->get('logger')->notice(sprintf('Game:inviteAnybody remove game:%s', $game->getId()));
                $this->cleanPlayerVersionsCache($game);
                return false;
            }
            $this->container->get('logger')->notice(sprintf('Game:inviteAnybody join game:%s, variant:%s, time:%s', $game->getId(), $game->getVariantName(), $game->getClockName()));
            return $game;
        }
        $this->container->get('logger')->notice(sprintf('Game:inviteAnybody queue game:%s, variant:%s, time:%s', $game->getId(), implode(',', $config->getVariantNames()), implode(',', $config->times)));
        return $game;
    }

    public function joinGame(Model\Game $game)
    {
        if($game->getIsStarted()) {
            $this->container->get('logger')->warn(sprintf('Game:join started game:%s', $game->getId()));
            return false;
        }

        $this->container->get('lichess.blamer.player')->blame($game->getInvited());
        $game->start();
        $game->getCreator()->addEventToStack(array(
            'type' => 'redirect',
            'url'  => $this->container->get('router')->generate('lichess_player', array('id' => $game->getCreator()->getFullId()))
        ));
        $this->container->get('lichess.object_manager')->flush();
        $this->container->get('logger')->notice(sprintf('Game:join game:%s, variant:%s, time:%d', $game->getId(), $game->getVariantName(), $game->getClockMinutes()));
        $this->cachePlayerVersions($game);

        return true;
    }

    public function getRecentlyStarted($page, $limit)
    {
        $query = $this->container->get('lichess.repository.game')->createRecentStartedOrFinishedQuery();

        return $this->createPaginatorForQuery($query, $page, $limit);
    }

    public function getRecentMates($page, $limit)
    {
        $query = $this->container->get('lichess.repository.game')->createRecentMateQuery();

        return $this->createPaginatorForQuery($query, $page, $limit);
    }

    protected function createPaginatorForQuery($query, $page, $limit)
    {
        $adapter = $this->container->getParameter('lichess.paginator.adapter.class');

        $games = new Paginator(new $adapter($query));

        $games->setCurrentPageNumber($page);
        $games->setItemCountPerPage($limit);
        $games->setPageRange($limit);

        return $games;
    }
}