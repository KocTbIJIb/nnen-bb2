<?php
$rootPath = dirname(__FILE__);
require_once $rootPath . '/vendor/autoload.php';
$env = null;
$envFile = $rootPath . '/.env';
if (is_file($envFile)) {
    $env = trim(file_get_contents($envFile));
}

$configFile = $rootPath . '/protected/config/main.php';
if (!empty($env)) {
    $configFile = $rootPath . '/protected/config/' . $env . '.php';
    if (!file_exists($configFile)) {
        die('Config file is not found.');
    }
}

$yii = $rootPath.'/vendor/yiisoft/yii/framework/yii.php';
$config = require $configFile;

require_once($yii);
Yii::setPathOfAlias('bootstrap', $rootPath . '/protected/extensions/bootstrap');
Yii::createWebApplication($config)->run();