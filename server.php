<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/app/env.php');
require_once(__DIR__ . '/version.php');

use TRD\Utility\ConsoleDebug;
use TRD\App;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

function e($number, $msg, $file, $line, $vars)
{
    ob_start();
    debug_print_backtrace();
    $error = ob_get_clean();

    $error = "Error $number\nMsg $msg\nFile $file\nLine $line\n" . var_export($vars, true) . "\n\n" . $error;

    file_put_contents('debug.log', $error, FILE_APPEND);
    die();
}
//set_error_handler('e');


$container = array();

/*
CONTAINER: DB
 */
$container['db'] = \Doctrine\DBAL\DriverManager::getConnection(array(
  'dbname' => $_ENV['DB_NAME'],
  'user' => $_ENV['DB_USERNAME'],
  'password' => $_ENV['DB_PASSWORD'],
  'host' => $_ENV['DB_HOST'],
  'port' => isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306,
  'driver' => $_ENV['DB_DRIVER'],
), new \Doctrine\DBAL\Configuration());


/*
CONTAINER: MEMCACHE
 */
$container['memcache'] = null;
if ($_ENV['CACHE_ENABLED']) {
    $memcache = new \Memcached;
    $memcache->addServer($_ENV['CACHE_HOST'], $_ENV['CACHE_PORT']);
    $container['memcache'] = $memcache;
}

/*
CONTAINER: MODELS
 */
$container['models'] = array(
  'sites' => new \TRD\Model\Sites($container['memcache']),
  'prebots' => new \TRD\Model\Prebots(),
  'settings' => new \TRD\Model\Settings($container['memcache']),
  'skiplists' => new \TRD\Model\Skiplist()
);

if ($_ENV['AUTOTRADING_ENABLED']) {
    $container['models']['autorules'] = new \TRD\Model\AutoRules();
}

$container['modelsMemory'] = array(
    'pretips' => array()
);

/*
CONTAINER: DATA_SOURCES
 */
$container['data_sources'] = array(
    'imdb' => new \TRD\DataProvider\IMDBDataProvider($container)
);

/*
CONTAINER: EVENT DISPATCHER
 */
$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new \TRD\EventSubscriber\CacheSubscriber($container));
$container['dispatcher'] = $dispatcher;

/*
CONTAINER: LOG
 */
$logger = new Logger('general');
$dateFormat = "Y-m-d H:i:s";
$output = "[%datetime%] %message% %context%\n";
$formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat, true);
$infoHandler = new StreamHandler($_ENV['LOG_PATH'] . '/general.info.log', Logger::INFO);
$infoHandler->setFormatter($formatter);
$debugHandler = new StreamHandler($_ENV['LOG_PATH'] . '/general.debug.log', Logger::DEBUG);
$debugHandler->setFormatter($formatter);
$logger->pushHandler($debugHandler);
$logger->pushHandler($infoHandler);
$logger->pushHandler(new StreamHandler($_ENV['LOG_PATH'] . '/general.notice.log', Logger::NOTICE));
$container['log'] = $logger;

/*
CONTAINER: DATALOG
 */
$dlogger = new Logger('datalog');
$dateFormat = "Y n j, g:i a";
$output = "[%datetime%] %message% %context%\n";
$formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat, true);
$debugHandler = new StreamHandler($_ENV['LOG_PATH'] . '/data.debug.log', Logger::DEBUG);
$debugHandler->setFormatter($formatter);
$dlogger->pushHandler($debugHandler);
$container['datalog'] = $dlogger;
$container['log']->debug('Starting server');


$clients = new \SplObjectStorage();
$app = new App($container);

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\TcpServer($_ENV['SERVER_HOST'] . ":" . $_ENV['SERVER_PORT'], $loop);
$socket->on('connection', function (React\Socket\ConnectionInterface $conn) use (&$app, $clients) {
    $clients->attach($conn);

    // greet the user
    $conn->write(json_encode([
      'command' => 'VERSION',
      'version' => TRD_VERSION
    ]));
    ConsoleDebug::debug('Client connected');

    // handle incoming messages
    $conn->on('data', function ($data) use (&$app, $conn, $clients) {
        // process the incoming message
        $app->process($clients, $data);
    });

    // if user goes
    $conn->on('end', function () use ($conn, $clients) {
        ConsoleDebug::debug('Client disconnected');
        $clients->detach($conn);
    });
});

// start the server
ConsoleDebug::debug(sprintf('TRD (%s) server started on port %d', TRD_VERSION, $_ENV['SERVER_PORT']));
$loop->run();
