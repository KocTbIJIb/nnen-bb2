<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);

return array_replace_recursive(
    require dirname(__FILE__) . '/main.php', 
    array(
        'components' => array(
            'mongodb' => array(
                'server' => 'mongodb://localhost:27017'
            ),
        ),
    )
);