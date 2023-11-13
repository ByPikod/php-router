<?php

require_once __DIR__ . '/vendor/autoload.php';

use PHPRouter\RouterTest;
use PHPTest\Test;

ini_set('memory_limit', '128MB');
$router = new RouterTest();
Test::suiteClass($router);
