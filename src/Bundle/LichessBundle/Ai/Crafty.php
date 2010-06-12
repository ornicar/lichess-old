<?php

namespace Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Notation\Forsythe;

class Crafty extends Ai
{
    protected $options = array(
        'level' => 1
    );

    public function move()
    {
        $forsythe = new Forsythe();
        $oldForsythe = $forsythe->export($this->player->getGame());
        $newForsythe = $this->getNewForsythe($oldForsythe);
        $move = $forsythe->diffToMove($this->player->getGame(), $newForsythe);

        return $move;
    }

    protected function getNewForsythe($forsytheNotation)
    {
        $file = sys_get_temp_dir().'/lichess/crafty_'.$this->player->getGame()->getHash();
        if(!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777);
        }
        touch($file);
        file_put_contents($file, '');
        chmod($file, 0777);

        $command = $this->getPlayCommand($forsytheNotation, $file);

        ob_start();
        passthru($command, $code);
        $return = ob_get_clean();

        if($code !== 0)
        {
            throw new \RuntimeException(sprintf('Can not run crafty: '.$command.' '.$return));
        }

        $forsythe = $this->extractForsythe(file($file, FILE_IGNORE_NEW_LINES));

        if(!$forsythe)
        {
            throw new \RuntimeException(sprintf('Can not run crafty: '.$command.' '.$return));
        }
        unlink($file);

        return $forsythe;
    }

    protected function extractForsythe($results)
    {
        return str_replace('setboard ', '', $results[0]);
    }

    protected function getPlayCommand($forsytheNotation, $file)
    {
        return sprintf("cd %s && %s log=off ponder=off %s <<EOF
            setboard %s
move
savepos %s
quit
EOF",
dirname($file),
'crafty',
$this->getCraftyLevel(),
$forsytheNotation,
basename($file)
    );
    }

    protected function getCraftyLevel()
    {
        /*
         * st is the time in seconds crafty can think about the situation
         */
        return "st=".(round($this->options['level']/12, 2));
    }
}
