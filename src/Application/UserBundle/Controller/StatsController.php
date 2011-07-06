<?php

namespace Application\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Lichess\ChartBundle\Chart\UsersEloChart;
use Lichess\ChartBundle\Chart\GameEndChart;

class StatsController extends ContainerAware
{
    public function indexAction()
    {
        $eloChart = new UsersEloChart($this->container->get('fos_user.repository.user'));
        $endChart = new GameEndChart($this->container->get('lichess.repository.game'));

        return $this->container->get('templating')->renderResponse('LichessUserBundle:Stats:index.html.twig', compact('eloChart', 'endChart'));
    }
}
