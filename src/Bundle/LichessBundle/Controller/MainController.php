<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Socket;
use Bundle\LichessBundle\Chess\Generator;

class MainController extends Controller
{

    public function indexAction($color)
    {

        $game = $this->getNewGame();
        $player = $game->getPlayer($color);
        $game->setCreator($player);

        if(isset($_SERVER['REMOTE_ADDR']) && '127.0.0.1' == $_SERVER['REMOTE_ADDR']) {
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

    protected function getNewGame()
    {
        $generator = new Generator();
        return $generator->createGame();
    }

    protected function getGameTestPromotion()
    {
        $generator = new Generator();
        $data = <<<EOF
       k
        
 PPPP   
        
        
   pppp 
        
K       
EOF;
        return $generator->createGameFromVisualBlock($data);
    }

    protected function getGameTestStalemate()
    {
        $generator = new Generator();
        $data = <<<EOF
       k
     K  
     Q  
        
        
        
        
        
EOF;
        return $generator->createGameFromVisualBlock($data);
    }
}
