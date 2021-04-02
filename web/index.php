<?php

$app = require_once(__DIR__ . '/../app/bootstrap.php');

// APIs for web gui
$app->mount('/api/section', new TRD\Controller\API\Section());
$app->mount('/api/skiplist', new TRD\Controller\API\Skiplist());
$app->mount('/api/site', new TRD\Controller\API\Site());
$app->mount('/api/cache', new TRD\Controller\API\Cache());
$app->mount('/api/doc', new TRD\Controller\API\Doc());
$app->mount('/api/settings', new TRD\Controller\API\Settings());
$app->mount('/api/autorules', new TRD\Controller\API\AutoRules());
$app->mount('/api/prebots', new TRD\Controller\API\Prebots());


// some frontend stuff
$app->mount('/site', new TRD\Controller\Site());
$app->mount('/race', new TRD\Controller\Race());
$app->mount('/approved', new TRD\Controller\Approved());
$app->mount('/autorules', new TRD\Controller\AutoRules());
$app->mount('/tools', new TRD\Controller\Tools());
$app->mount('/settings', new TRD\Controller\Settings());
$app->mount('/cache', new TRD\Controller\Cache());
$app->mount('/skiplist', new TRD\Controller\Skiplist());
$app->mount('/prebots', new TRD\Controller\Prebots());

$app->get('', function () use ($app) {
    return $app->redirect('/site/list');
});

$app->run();
