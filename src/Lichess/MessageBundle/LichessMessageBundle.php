<?php

namespace Lichess\MessageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LichessMessageBundle extends Bundle
{
    public function getParent()
    {
        return 'OrnicarMessageBundle';
    }
}
