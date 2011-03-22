<?php

$vendorDir = realpath(__DIR__.'/../vendor');
$srcDir = realpath(__DIR__.'/../src');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                        => $vendorDir.'/symfony/src',
    'Doctrine\\MongoDB'              => $vendorDir.'/mongodb-odm/lib/vendor/doctrine-mongodb/lib',
    'Doctrine\\ODM\\MongoDB'         => $vendorDir.'/mongodb-odm/lib',
    'Doctrine\\Common'               => $vendorDir.'/mongodb-odm/lib/vendor/doctrine-common/lib',
    'Bundle'                         => $srcDir,
    'FOS'                            => $srcDir,
    'Application'                    => $srcDir
));
$loader->register();
