<?php

namespace Bundle\LichessBundle;

use Symfony\Component\Console\Input\ArgvInput;

class ConsoleGuardRail
{
    protected $productionBlackList = array(
        'doctrine:mongodb:data:load',
    );

    public function isSafe(ArgvInput $input, $env)
    {
        if ('prod' == $env) {
            if (in_array($input->getFirstArgument(), $this->productionBlackList)) {
                return false;
            }
        }

        return true;
    }
}
