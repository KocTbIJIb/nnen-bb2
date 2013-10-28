<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 1);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', true); 

return array_replace_recursive(
    require dirname(__FILE__) . '/main.php', 
    array(
        'components' => array(
            'db'=>array(
                'connectionString' => 'mysql:host=nnen.ru;dbname=bb',
                'emulatePrepare' => true,
                'username' => 'bb',
                'password' => 'RLYVpUSAv7P5YHXA',
                'charset' => 'utf8',
            )
        )
    )
);