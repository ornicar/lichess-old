<?php

namespace Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Notation\Forsythe;
use Bundle\LichessBundle\Entities\Game;

class Crafty
{
    public function move(Game $game, $level)
    {
        $forsythe = new Forsythe();
        $oldForsythe = $forsythe->export($game);
        $newForsythe = $this->getNewForsythe($oldForsythe, $level);
        $move = $forsythe->diffToMove($game, $newForsythe);

        return $move;
    }

    protected function getNewForsythe($forsytheNotation, $level)
    {
        $file = tempnam(sys_get_temp_dir(), 'lichess_crafty_'.md5(time().mt_rand(0, 1000)));
        touch($file);

        $command = $this->getPlayCommand($forsytheNotation, $file, $level);
        exec($command, $output, $code);
        if($code !== 0)
        {
            throw new \RuntimeException(sprintf('Can not run crafty: '.$command.' '.implode("\n", $output)));
        }

        $forsythe = $this->extractForsythe(file($file, FILE_IGNORE_NEW_LINES));
        unlink($file);

        if(!$forsythe)
        {
            throw new \RuntimeException(sprintf('Can not run crafty: '.$command.' '.implode("\n", $output)));
        }

        return $forsythe;
    }

    protected function extractForsythe($results)
    {
        return str_replace('setboard ', '', $results[0]);
    }

    protected function getPlayCommand($forsytheNotation, $file, $level)
    {
        return sprintf("cd %s && %s log=off ponder=off smpmt=1 %s <<EOF
setboard %s
move
savepos %s
quit
EOF",
            dirname($file),
            '/usr/games/crafty',
            $this->getCraftyLevel($level),
            $forsytheNotation,
            basename($file)
        );
    }

    protected function getCraftyLevel($level)
    {
        $config = array(
            /*
            * sd is the number of moves crafty can anticipate
            */
            'sd='.$level,
            /*
            * st is the time in seconds crafty can think about the situation
            */
            'st='.(round($level/10, 2)),
        );

        if($level < 4) {
            $config[] = 'book=off';
        }

        return implode(' ', $config);
    }
}
