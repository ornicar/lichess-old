<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Socket;

class MainController extends Controller
{

    public function indexAction($color)
    {
        $game = $this->container->getLichessGeneratorService()->createGame();
        $player = $game->getPlayer($color);
        $game->setCreator($player);

        if(isset($_SERVER['HTTP_USER_AGENT']) && 0 === strncmp($_SERVER['HTTP_USER_AGENT'], 'Wget/', 5)) {
            // When munin pings the website, don't save the new game
        }
        else {
            $this->container->getLichessPersistenceService()->save($game);
            $socket = new Socket($player, $this->container['kernel.root_dir'].'/cache/socket');
            $socket->write(array());
        }

        return $this->render('LichessBundle:Main:index', array(
            'player' => $player
        ));
    }

    public function aboutAction()
    {
        return $this->render('LichessBundle:Main:about');
    }

    public function notFoundAction()
    {
        error_log(sprintf('404 %s [%s]', $this->getRequest()->getRequestUri(), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?'));
        $response = $this->render('LichessBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
