<?php

$vendorDir  = realpath(__DIR__.'/../vendor');
$bundleDir = __DIR__.'/../vendor/bundles';
$srcDir     = realpath(__DIR__.'/../src');

require $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\ApcUniversalClassLoader('lichess.cl.');

$loader->registerNamespaces(array(
    'Symfony'                => array($vendorDir.'/symfony/src', $bundleDir),
    'Doctrine\\MongoDB'      => $vendorDir.'/doctrine-mongodb/lib',
    'Doctrine\\ODM\\MongoDB' => $vendorDir.'/doctrine-mongodb-odm/lib',
    'Doctrine\\Common'       => $vendorDir.'/doctrine-common/lib',
    'FOS'                    => $bundleDir,
    'Ornicar'                => $bundleDir,
));
$loader->registerNamespaceFallbacks(array(
    __DIR__.'/../src',
));
$loader->register();

// doctrine annotations
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
Doctrine\Common\Annotations\AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine-mongodb-odm/lib/Doctrine/ODM/MongoDB/Mapping/Annotations/DoctrineAnnotations.php');
