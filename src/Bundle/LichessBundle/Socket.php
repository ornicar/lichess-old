<?php

namespace Bundle\LichessBundle;

use Bundle\LichessBundle\Entities\Player;

/**
 * A socket ensures lightning fast communication through ajax.
 * It creates a public cache file used by Javascript to gather informations.
 * This method saves a lot of PHP requests.
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Socket
{
    const WAIT = 'wait';
    const START = 'start';
    const PLAY = 'play';
    const UPDATE = 'update';
    
    /**
     * The absolute directory where to store the socket
     *
     * @var string
     */
    protected $dir = null;
    
    /**
     * Instanciate a Socket
     **/
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Write structured data to the socket
     *
     * @return null
     **/
    public function write(Player $player, array $data)
    {
        $data['time'] = time();
        file_put_contents($this->getFile($player), json_encode($data));
    }
    
    /**
     * Get dir
     * @return string
     */
    public function getDir()
    {
      return $this->dir;
    }
    
    /**
     * Set dir
     * @param  string
     * @return null
     */
    public function setDir($dir)
    {
      $this->dir = $dir;
    }

    /**
     * Get the socket file
     *
     * @return string
     **/
    public function getFile(Player $player)
    {
        return $this->dir.'/'.$player->getFullHash().'.json';
    }
}
