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
     * The player this socket communicates with
     *
     * @var Player
     */
    protected $player = null;
    
    /**
     * The absolute directory where to store the socket
     *
     * @var string
     */
    protected $dir = null;
    
    /**
     * Instanciate a Socket
     **/
    public function __construct(Player $player, $dir)
    {
        $this->player = $player;
        $this->dir = $dir;
        if(!is_dir($dir)) {
            mkdir($dir);
        }
        if(!is_writable($dir)) {
            throw new \InvalidArgumentException($dir.' is not writable');
        }
    }

    /**
     * Write structured data to the socket
     *
     * @return null
     **/
    public function write(array $data)
    {
        $data['time'] = time();
        $json = json_encode($data);
        file_put_contents($this->getFile(), $json);
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
     * Get player
     * @return Player
     */
    public function getPlayer()
    {
      return $this->player;
    }
    
    /**
     * Set player
     * @param  Player
     * @return null
     */
    public function setPlayer($player)
    {
      $this->player = $player;
    }

    /**
     * Get the socket file
     *
     * @return string
     **/
    public function getFile()
    {
        return $this->dir.'/'.$this->player->getFullHash().'.json';
    }
}
