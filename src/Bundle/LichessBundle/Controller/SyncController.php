<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class SyncController implements ContainerAwareInterface
{
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function syncAction($id, $color, $version, $playerFullId)
    {
        $player = $this->container->get('lichess.provider')->findPublicPlayer($id, $color);
        if (!$player) {
            $data = array('reload' => true);
        } else {
            $isSigned = $player->getFullId() === $playerFullId;
            $data     = $this->container->get('lichess.synchronizer')->synchronize($player, $version, $isSigned);
        }

        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
