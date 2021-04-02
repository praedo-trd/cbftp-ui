<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\EventDispatcher\EventDispatcher;

use Monolog\Logger;

require_once('env.php');

$app = new Silex\Application();

// turn on debug mode
$app['debug'] = DEBUG_MODE;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host'      => $_ENV['DB_HOST'],
        'port'      => isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306,
        'dbname'    => $_ENV['DB_NAME'],
        'user'      => $_ENV['DB_USERNAME'],
        'password'  => $_ENV['DB_PASSWORD'],
    ),
));

$app['memcache'] = null;
if ($_ENV['CACHE_ENABLED']) {
    $app['memcache'] = $app->share(function () {
        $memcache = new \Memcached;
        $memcache->addServer($_ENV['CACHE_HOST'], $_ENV['CACHE_PORT']);
        return $memcache;
    });
}

// $app['models'] = function () use ($app) {
//     return array(
//         'sites' =>
//         ,'prebots' => new \TRD\Model\Prebots()
//         ,'settings' => new \TRD\Model\Settings($app['memcache'])
//         ,'skiplists' => new \TRD\Model\Skiplist()
//     );
// };

$app['models'] = array(
  'sites' => new \TRD\Model\Sites(),
  'prebots' => new \TRD\Model\Prebots(),
  'settings' => new \TRD\Model\Settings($app['memcache']),
  'skiplists' => new \TRD\Model\Skiplist()
);

$app['modelsMemory'] = array(
    'pretips' => array()
);

$app['log'] = $app->share(function () use ($app) {
    $logger = new Logger('general');


    return $logger;
});

$app['datalog'] = $app->share(function () use ($app) {
    $logger = new Logger('datalog');
    return $logger;
});

$app['dispatcher'] = $app->share(function () use ($app) {
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new \TRD\EventSubscriber\CacheSubscriber($app));
    return $dispatcher;
});

return $app;
