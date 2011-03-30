<?php

namespace Application\ForumBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LichessForumBundle extends Bundle
{
    public function getParent()
    {
        return 'Forum';
    }
}
