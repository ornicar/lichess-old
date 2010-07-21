<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Framework/UniversalClassLoader.php';

$loader = new Symfony\Framework\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'     => __DIR__.'/vendor/Symfony/src',
    'Bundle'      => __DIR__,
    'Zend'        => __DIR__.'/vendor/Zend/library',
));
$loader->register();
