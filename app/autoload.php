<?php

$vendorDir = __DIR__.'/../vendor';
$bundleDir = __DIR__.'/../vendor/bundles';

require $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\ApcUniversalClassLoader('lichess2.cl.');

$loader->registerNamespaces(array(
    'Symfony'                        => array($vendorDir.'/symfony/src', $bundleDir),
    'Doctrine\\MongoDB'              => $vendorDir.'/doctrine-mongodb/lib',
    'Doctrine\\ODM\\MongoDB'         => $vendorDir.'/doctrine-mongodb-odm/lib',
    'Doctrine\\Common\\DataFixtures' => $vendorDir.'/doctrine-data-fixtures/lib',
    'Doctrine\\Common'               => $vendorDir.'/doctrine-common/lib',
    'Zend'                           => $vendorDir.'/zend-subtrees',
    'Monolog'                        => $vendorDir.'/monolog/src',
    'Assetic'                        => $vendorDir.'/assetic/src',
    'Pagerfanta'                     => $vendorDir.'/pagerfanta/src',
    'Gedmo'                          => $vendorDir.'/doctrine-extensions/lib',
    'EasyCSV'                        => $vendorDir.'/easy-csv/lib',
    'Buzz'                           => $vendorDir.'/buzz/lib',
    'Ornicar'                        => $bundleDir,
    'Sensio'                         => $bundleDir,
    'WhiteOctober'                   => $bundleDir,
    'FOS'                            => $bundleDir,
    'Herzult'                        => $bundleDir,
    'Bundle'                         => $bundleDir,
    'FOQ'                            => $bundleDir
));
$loader->registerPrefixes(array(
    'Twig_'  => $vendorDir.'/twig/lib',
    'Elastica_'  => $vendorDir.'/elastica/lib'
));
$loader->registerPrefixFallbacks(array(
    __DIR__.'/../vendor/symfony/src/Symfony/Component/Locale/Resources/stubs',
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
