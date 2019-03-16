<?php

header("charset=utf-8");
error_reporting(E_ERROR);
define('APP_PATH', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

$app  = new \Yaf\Application(APP_PATH . '/conf/application.ini', ini_get('yaf.environ'));

$app->getDispatcher()->catchException(true);

$app->bootstrap()->run();