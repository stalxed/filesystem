<?php
error_reporting(E_ALL | E_STRICT);

$stRoot = realpath(dirname(dirname(__DIR__)));
$stCoreLibrary = $stRoot . '/library/';
$stCoreUnitTests = $stRoot . '/tests/unit/';

$paths = array(
    $stCoreLibrary,
    $stCoreUnitTests,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $paths));

require_once $stRoot . '/vendor/autoload.php';

use Stalxed\System\ClassLoader;

$cl1 = new ClassLoader('Stalxed\FileSystem');
$cl1->register();

$cl2 = new ClassLoader('StalxedTest\FileSystem');
$cl2->register();

unset($stRoot, $stCoreLibrary, $stCoreUnitTests, $paths);
