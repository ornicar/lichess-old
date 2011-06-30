<?php

$vendorDir  = realpath(__DIR__.'/../vendor');
$srcDir     = realpath(__DIR__.'/../src');

require $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\ApcUniversalClassLoader('lichess.cl.');

$loader->registerNamespaces(array(
    'Symfony'                => array($vendorDir.'/symfony/src', $srcDir),
    'Doctrine\\MongoDB'      => $vendorDir.'/doctrine-mongodb/lib',
    'Doctrine\\ODM\\MongoDB' => $vendorDir.'/doctrine-mongodb-odm/lib',
    'Doctrine\\Common'       => $vendorDir.'/doctrine-common/lib',
    'Bundle'                 => $srcDir,
    'FOS'                    => $srcDir,
    'Application'            => $srcDir
));
$loader->register();
