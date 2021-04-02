<?php

require_once(__DIR__ . '/../app/env.php');
require_once __DIR__ .'/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;

$container = new Pimple();
$container['db'] = $container->share(function () {
    return \Doctrine\DBAL\DriverManager::getConnection(array(
      'dbname' => $_ENV['DB_NAME'],
      'user' => $_ENV['DB_USERNAME'],
      'password' => $_ENV['DB_PASSWORD'],
      'host' => $_ENV['DB_HOST'],
      'port' => isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306,
      'driver' => $_ENV['DB_DRIVER'],
  ), new \Doctrine\DBAL\Configuration());
});

$container['models'] = function () {
    return array(
      'sites' => new \TRD\Model\Sites()
      ,'prebots' => new \TRD\Model\Prebots()
      ,'settings' => new \TRD\Model\Settings()
      ,'skiplists' => new \TRD\Model\Skiplist()
  );
};

$container['dispatcher'] = $container->share(function () use ($container) {
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new \TRD\EventSubscriber\CacheSubscriber($container));
    return $dispatcher;
});

return $container;
