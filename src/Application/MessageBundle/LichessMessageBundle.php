<?php

namespace Application\MessageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LichessMessageBundle extends Bundle
{
    public function getParent()
    {
        return 'OrnicarMessageBundle';
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
