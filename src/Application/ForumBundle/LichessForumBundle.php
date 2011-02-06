<?php

namespace Application\ForumBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LichessForumBundle extends Bundle
{
    public function getParent()
    {
        return 'ForumBundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
