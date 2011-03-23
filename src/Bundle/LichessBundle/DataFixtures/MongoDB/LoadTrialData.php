<?php

namespace Bundle\LichessBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Bundle\LichessBundle\Document\Trial;

class LoadTrialData implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $gameRepository;

    public function getOrder()
    {
        return 4;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->gameRepository        = $container->get('lichess.repository.game');
    }

    public function load($manager)
    {
        $mates = $this->gameRepository->createRecentMateQuery()->getQuery()->execute();

        foreach ($mates as $mate) {
            $trial = new Trial();
            $trial->setGame($mate);
            $trial->setUser($mate->getWinner()->getUser());
            $trial->setScore(70);
            $manager->persist($trial);
        }

        $manager->flush();
    }
}
