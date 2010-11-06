<?php

namespace Bundle\LichessBundle\Util;

class KeyGenerator
{
    static public function generate($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_-';
        $nbChars = strlen($chars);

        $key = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $key .= $chars[mt_rand(0, $nbChars-1)];
        }

        return $key;
    }
}
