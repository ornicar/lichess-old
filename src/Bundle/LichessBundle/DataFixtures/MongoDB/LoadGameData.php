<?php

namespace Bundle\LichessBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Bundle\LichessBundle\Config\AiGameConfig;
use Bundle\LichessBundle\Config\FriendGameConfig;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Stack;

class LoadGameData implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $userManager;
    protected $aiStarter;
    protected $friendStarter;

    public function getOrder()
    {
        return 0;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager   = $container->get('fos_user.user_manager');
        $this->aiStarter     = $container->get('lichess.starter.ai');
        $this->friendStarter = $container->get('lichess.starter.friend');
    }

    public function load($manager)
    {
        $this->loadAiGame('white', null);
        $this->loadAiGame('black', null);

        $this->loadAiGame('white', 'user1');
        $this->loadAiGame('black', 'user1');

        $this->loadFriendGame('white', null, null, array('time' => 10));

        $this->loadFriendGame('white', 'user1', null, array('time' => 5, 'increment' => 10));
        $this->loadFriendGame('white', null, 'user1', array('time' => 2, 'increment' => 20));

        $this->loadFriendGame('black', 'user1', 'user2', array('time' => 20, 'increment' => 5));
        $this->loadFriendGame('black', 'user2', 'user1');
    }

    protected function loadAiGame($color, $username)
    {
        $config = new AiGameConfig();
        $config->color = $color;
        $player = $this->aiStarter->start($config);
        $game = $player->getGame();
        if ($username) {
            $this->blamePlayerWithUsername($player, $username);
        }
        $manipulator = new Manipulator($game, new Stack());
        if ('white' === $color) {
            $manipulator->play('d2 d4');
        } else {
            $manipulator->play('g8 h6');
        }
    }

    protected function loadFriendGame($color, $username1, $username2, array $configArray = array())
    {
        $config = new FriendGameConfig();
        $config->fromArray($configArray);
        $config->color = $color;
        $player = $this->friendStarter->start($config);
        $game = $player->getGame();
        if ($username1) {
            $this->blamePlayerWithUsername($player, $username1);
        }
        if ($username2) {
            $this->blamePlayerWithUsername($player->getOpponent(), $username2);
        }
        $game->start();
        $manipulator = new Manipulator($game, new Stack());
        $manipulator->play('d2 d4');
        $manipulator->play('e7 e5');
        $manipulator->play('b1 c3');
    }

    protected function blamePlayerWithUsername($player, $username)
    {
        $user = $this->userManager->findUserByUsername($username);
        $player->setUser($user);
    }
}
