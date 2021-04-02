<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once('env.php');

use Silex\Application;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TRD\Twig\Extension\GetEnvExtension;

function is_valid_bind_address($address)
{
    return filter_var(
      $address,
      FILTER_VALIDATE_IP,
      FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
  ) === false;
}
if (!is_valid_bind_address($_SERVER['SERVER_NAME'])) {
    die('Always keep your web GUI on an internal network. This is a HUGE security issue to run on external IP');
}

if (empty($_ENV['APP_TIMEZONE'])) {
    die('Set your APP_TIMEZONE environmental variable');
}
date_default_timezone_set($_ENV['APP_TIMEZONE']);

$app = new Application();
$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\HttpFragmentServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => $_ENV['DB_DRIVER'],
        'host'      => $_ENV['DB_HOST'],
        'port'      => isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306,
        'dbname'    => $_ENV['DB_NAME'],
        'user'      => $_ENV['DB_USERNAME'],
        'password'  => $_ENV['DB_PASSWORD'],
    ),
));
$app['db'] = $app->share($app->extend('db', function ($db, $app) {
    $db->executeQuery(sprintf("SET session time_zone = '%s';", $_ENV['APP_TIMEZONE']));
    return $db;
}));

$app['models'] = function () {
    $models = array(
        'sites' => new \TRD\Model\Sites()
        ,'prebots' => new \TRD\Model\Prebots()
        ,'settings' => new \TRD\Model\Settings()
        ,'skiplists' => new \TRD\Model\Skiplist()
    );
    if ($_ENV['AUTOTRADING_ENABLED']) {
        $models['autorules'] = new \TRD\Model\AutoRules();
    }
    return $models;
};

$app['modelsMemory'] = array(
    'pretips' => array()
);


$app['data_sources'] = $app->share(function () use ($app) {
    return array(
        'imdb' => new \TRD\DataProvider\IMDBDataProvider($app)
        ,'tvrage' => new \TRD\DataProvider\TVRageDataProvider($app)
    );
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
));

function formatSizeUnits($megaBytes)
{
    if ($megaBytes > 1000000) {
        return round($megaBytes / 1024 / 1024, 2) . "TB";
    } elseif ($megaBytes > 1000) {
        return round(($megaBytes / 1024), 1) . "GB";
    } else {
        return floor($megaBytes) . "MB";
    }

    return round($gigaBytes, 1);
}

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addExtension(new \Twig_Extensions_Extension_Date());
    $twig->addExtension(new TRD\Twig\Extension\IsBooleanExtension());
    $twig->addExtension(new TRD\Twig\Extension\GetEnvExtension());
    $twig->addFilter(new Twig_SimpleFilter('format_credits', function ($creditsObject) {
        $total = 0;
        foreach ($creditsObject as $bnc => $value) {
            $total += $value;
        }
        
        return formatSizeUnits($total);
    }));
    $twig->addFilter(new Twig_SimpleFilter('format_individual_credits', function ($creditsObject) {
        $credits = array();
        foreach ($creditsObject as $bnc => $value) {
            $credits[$bnc] = formatSizeUnits($value);
        }
        ksort($credits, SORT_NATURAL);
        return $credits;
    }));
    return $twig;
}));

if ($_ENV['ENABLE_WEB_PROFILER']) {
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
      'profiler.cache_dir' => __DIR__.'/../cache/profiler',
      'profiler.mount_prefix' => '/_profiler', // this is the default
  ));
    $app->register(new Sorien\Provider\DoctrineProfilerServiceProvider());
}

$app['log'] = $app->share(function () use ($app) {
    $logger = new Logger('general');

    // the default date format is "Y-m-d H:i:s"
    $dateFormat = "Y n j, g:i a";
    // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
    $output = "[%datetime%] %message% %context%\n";
    // finally, create a formatter
    $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat, true);

    $infoHandler = new StreamHandler($_ENV['LOG_PATH'] . '/general.info.log', Logger::INFO, false);
    $infoHandler->setFormatter($formatter);

    $debugHandler = new StreamHandler($_ENV['LOG_PATH'] . '/general.debug.log', Logger::DEBUG, false);
    $debugHandler->setFormatter($formatter);

    $logger->pushHandler($debugHandler);
    $logger->pushHandler($infoHandler);
    $logger->pushHandler(new StreamHandler($_ENV['LOG_PATH'] . '/general.notice.log', Logger::NOTICE, false));

    return $logger;
});

$app['datalog'] = $app->share(function () use ($app) {
    $logger = new Logger('datalog');

    // the default date format is "Y-m-d H:i:s"
    $dateFormat = "Y-m-d h:i:s";
    // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
    $output = "[%datetime%] %message% %context%\n";
    // finally, create a formatter
    $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat, true);

    $debugHandler = new StreamHandler($_ENV['LOG_PATH'] . '/data.debug.log', Logger::DEBUG);
    $debugHandler->setFormatter($formatter);

    $logger->pushHandler($debugHandler);
    return $logger;
});

return $app;
