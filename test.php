<?php

require_once __DIR__ . '/vendor/autoload.php';

use PHPRouter\RouterTest;
use PHPTest\Test;

$router = new RouterTest();
Test::suiteClass($router);
