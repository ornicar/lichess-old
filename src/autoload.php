<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

$loader = new Symfony\Component\HttpFoundation\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Zend'                   => __DIR__.'/vendor/zend/library',
    'Symfony'                => __DIR__.'/vendor/Symfony/src',
    'Doctrine\\Common'       => __DIR__.'/vendor/mongodb-odm/lib/vendor/doctrine-common/lib',
    'Doctrine\\ODM\\MongoDB' => __DIR__.'/vendor/mongodb-odm/lib',
    'Bundle'                 => __DIR__,
    'Application'            => __DIR__
));
$loader->register();
