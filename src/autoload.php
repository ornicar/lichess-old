<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Foundation/UniversalClassLoader.php';

use Symfony\Foundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
  'Symfony'     => __DIR__.'/vendor/Symfony/src',
  'Bundle'      => __DIR__
));
$loader->registerPrefixes(array(
  'Zend_'  => __DIR__.'/vendor/zend/library',
));
$loader->register();

set_include_path(__DIR__.'/vendor/zend/library'.PATH_SEPARATOR.get_include_path());
