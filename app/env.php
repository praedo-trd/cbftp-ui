<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Dotenv\Dotenv;

// basic global config
error_reporting(E_ERROR | E_PARSE);
define('DEBUG_MODE', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');
