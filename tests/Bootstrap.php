<?php
error_reporting(E_ALL | E_STRICT);

$stRoot = realpath(dirname(__DIR__));

require_once $stRoot . '/vendor/autoload.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
   'Stalxed\\FileSystem'     => $stRoot . '/library/',
   'StalxedTest\\FileSystem' => $stRoot . '/tests/unit/'
));
$loader->register();

unset($stRoot);
