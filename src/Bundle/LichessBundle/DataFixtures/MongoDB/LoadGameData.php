<?php

namespace Bundle\LichessBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Game;
use Lichess\OpeningBundle\Config\AiGameConfig;
use Lichess\OpeningBundle\Config\GameConfig;

class LoadGameData implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $userManager;
    protected $aiStarter;
    protected $friendStarter;
    protected $manipulatorFactory;

    public function getOrder()
    {
        return 1;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager        = $container->get('fos_user.user_manager');
        $this->aiStarter          = $container->get('lichess.starter.ai');
        $this->friendStarter      = $container->get('lichess.starter.friend');
        $this->manipulatorFactory = $container->get('lichess.manipulator_factory');
        if ($container->get('lichess.repository.game')->getNbGames() > 1000) {
            throw new \Exception('Refuse to erase prod data');
        }
    }

    public function load($manager)
    {
        $this->loadAiGame('white', null);
        $this->loadAiGame('black', null);

        $game = $this->loadAiGame('white', 'user1');
        $this->win($game);

        $game = $this->loadAiGame('black', 'user1');
        $this->win($game);

        $game = $this->loadFriendGame('white', null, null, array('mode' => 1, 'clock' => true, 'time' => 10));
        $this->applyMoves($game, array('d2 d4', 'e7 e5', 'b1 c3'));

        $game = $this->loadFriendGame('white', 'user1', null, array('mode' => 1, 'clock' => true, 'time' => 5, 'increment' => 10));
        $this->applyMoves($game, array('d2 d4', 'e7 e5', 'b1 c3'));
        $this->win($game);

        $game = $this->loadFriendGame('white', null, 'user1', array('mode' => 1, 'clock' => true, 'time' => 2, 'increment' => 20));
        $this->applyMoves($game, array('d2 d4', 'e7 e5', 'b1 c3'));
        $this->win($game);

        $game = $this->loadFriendGame('black', 'user1', 'user2', array('mode' => 1, 'clock' => true, 'time' => 20, 'increment' => 5, 'mode' => 1));
        $game->incrementBlurs('white');
        $game->incrementBlurs('white');
        $game->incrementBlurs('black');
        $this->win($game, 'black');

        $game = $this->loadFriendGame('black', 'user2', 'user1', array('mode' => 1));
        $game->incrementBlurs('black');
        $this->win($game);

        $game = $this->loadFriendGame('white', 'user1', 'user2', array('mode' => 1));
        $this->applyMoves($game, array('e2 e4', 'c7 c5', 'c2 c3', 'd7 d5', 'e4 d5', 'd8 d5', 'd2 d4', 'g8 f6', 'g1 f3', 'c8 g4', 'f1 e2', 'e7 e6', 'h2 h3', 'g4 h5', 'e1 g1'));

        $game = $this->loadFriendGame('white', 'user1', 'user2', array('mode' => 1));
        $this->applyMoves($game, array('e2 e4', 'c7 c5', 'c2 c3', 'd7 d5', 'e4 d5', 'd8 d5', 'd2 d4', 'g8 f6', 'g1 f3', 'c8 g4', 'f1 e2', 'e7 e6', 'h2 h3', 'g4 h5', 'e1 g1'));
        $this->win($game);

        $game = $this->loadFriendGame('white', 'user1', 'user2', array('mode' => 1));
        $this->applyMoves($game, array( 'e2 e4', 'd7 d5', 'e4 d5', 'd8 d5', 'b1 c3', 'd5 a5', 'd2 d4', 'c7 c6', 'g1 f3', 'c8 g4', 'c1 f4', 'e7 e6', 'h2 h3', 'g4 f3', 'd1 f3', 'f8 b4', 'f1 e2', 'b8 d7', 'a2 a3', 'e8 c8'));
        $this->win($game);

        $game = $this->loadFriendGame('white', 'user1', 'user2', array('mode' => 1));
        $this->applyMoves($game, array('d2 d4', 'e7 e5', 'b1 c3'));
        $this->win($game);

        $game = $this->loadFriendGame('white', 'user1', 'user3', array('mode' => 1));
        $this->applyMoves($game, array('d2 d4', 'e7 e5', 'b1 c3'));
        $this->win($game);

        $game = $this->loadFriendGame('white', 'user4', 'user1', array('mode' => 1));
        $this->applyMoves($game, array('d2 d4', 'e7 e5', 'b1 c3'));
        $this->win($game);

        $manager->flush();
    }

    protected function loadAiGame($color, $username)
    {
        $config = new AiGameConfig();
        $config->setColor($color);
        $player = $this->aiStarter->start($config);
        $game = $player->getGame();
        if ($username) {
            $this->blamePlayerWithUsername($player, $username);
        }
        $manipulator = $this->manipulatorFactory->create($game);
        if ('white' === $color) {
            $manipulator->play('d2 d4');
        } else {
            $manipulator->play('g8 h6');
        }

        return $game;
    }

    protected function loadFriendGame($color, $username1, $username2, array $configArray = array())
    {
        $config = new GameConfig();
        $config->fromArray($configArray);
        $config->setColor($color);
        $player = $this->friendStarter->start($config);
        $game = $player->getGame();
        if ($username1) {
            $this->blamePlayerWithUsername($player, $username1);
        }
        if ($username2) {
            $this->blamePlayerWithUsername($player->getOpponent(), $username2);
        }
        $game->start();

        return $game;
    }

    protected function blamePlayerWithUsername($player, $username)
    {
        $user = $this->userManager->findUserByUsername($username);
        $player->setUser($user);
    }

    protected function applyMoves(Game $game, array $moves)
    {
        $manipulator = $this->manipulatorFactory->create($game);
        foreach ($moves as $move) {
            $manipulator->play($move);
        }
    }

    protected function win(Game $game, $color = 'white')
    {
        //$this->finisher->finish($game, Game::MATE, $game->getPlayer($color));
    }
}
