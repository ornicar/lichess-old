<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;

class MainController extends Controller
{

    public function indexAction()
    {
        $logFile = $this->container['kernel.root_dir'].'/logs/'.$this->container['kernel.environment'].'.log';
        $log = file_get_contents($logFile);
        return $this->render('LichessBundle:Main:index', array(
            'log' => $log
        ));
    }
}
