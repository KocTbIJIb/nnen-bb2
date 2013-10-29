<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);

return array_replace_recursive(
    require dirname(__FILE__) . '/main.php', 
    array(
        'components' => array(
            'db'=>array(
                'connectionString' => 'mysql:host=localhost;dbname=bb',
                'emulatePrepare' => true,
                'username' => 'bb',
                'password' => 'RLYVpUSAv7P5YHXA',
                'charset' => 'utf8',
            )
        ),
    )
);