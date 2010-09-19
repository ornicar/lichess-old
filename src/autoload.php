<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

$loader = new Symfony\Component\HttpFoundation\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'     => __DIR__.'/vendor/Symfony/src',
    'Bundle'      => __DIR__
));
$loader->register();
