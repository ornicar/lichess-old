<?php

namespace Application\CommentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LichessCommentBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSComment';
    }
}
