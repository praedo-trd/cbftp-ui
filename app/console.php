<?php

require_once(__DIR__ . '/../app/env.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Console\Application;

$console = new Application;

$tasks = array();
foreach (new DirectoryIterator(__DIR__ . '/../src/TRD/Task') as $fileInfo) {
    if ($fileInfo->isDot()) {
        continue;
    }

    $class = 'TRD\\Task\\' . str_replace('.' . $fileInfo->getExtension(), '', $fileInfo->getFilename());

    $console->add(new $class());
}

$console->run();
