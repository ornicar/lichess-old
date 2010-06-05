<?php

class dmChessAiDriverCrafty extends dmChessAiDriver
{
  protected
  $serviceContainer;
  
  public function __construct(DmChessPlayer $player, dmBaseServiceContainer $serviceContainer, array $options = array())
  {
    $this->player           = $player;
    $this->serviceContainer = $serviceContainer;
    
    $this->initialize($options);
  }
  
  public function move()
  {
    $oldForsythe = $this->serviceContainer->get('dm_chess_forsythe')->gameToForsythe($this->player->Game);
    
    $newForsythe = $this->getCrafty()->execute($oldForsythe);
    
    $move = $this->serviceContainer->get('dm_chess_forsythe')->diffToMove($this->player->Game, $newForsythe);
    
    if (!$this->player->movePieceToSquare($move['from']->getPiece(), $move['to']))
    {
      throw new dmException('Illegal move: '.$move['from'].'->'.$move['to']);
    }
    
    return true;
  }
  
  protected function getCrafty()
  {
    $crafty = $this->serviceContainer->getService('dm_chess_crafty');
    $crafty->setOption('level', $this->getOption('level'));
    
    return $crafty;
  }
  
}