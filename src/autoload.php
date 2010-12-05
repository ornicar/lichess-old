<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

$loader = new Symfony\Component\HttpFoundation\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                => __DIR__.'/vendor/Symfony/src',
    'DoctrineExtensions'     => __DIR__.'/vendor/DoctrineExtensions/lib',
    'Doctrine\\Common'       => __DIR__.'/vendor/Doctrine/lib/vendor/doctrine-common/lib',
    'Doctrine\\DBAL'         => __DIR__.'/vendor/Doctrine/lib/vendor/doctrine-dbal/lib',
    'Doctrine'               => __DIR__.'/vendor/Doctrine/lib',
    'Bundle'                 => __DIR__,
    'ZendPaginatorAdapter'   => __DIR__.'/vendor/ZendPaginatorAdapter/src',
    'Zend'                   => __DIR__.'/vendor/zend/library',
    'Application'            => __DIR__
));
$loader->registerPrefixes(array(
    'Twig_'  => __DIR__.'/vendor/twig/lib'
));
$loader->register();

// hack to make Zend work
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/vendor/zend/library');
