<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Breaking Bad 2',

    // preloading 'log' component
    'preload'=>array('log'),

    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',
    ),

    'modules'=>array(
        // uncomment the following to enable the Gii tool
        /*
        'gii'=>array(
            'class'=>'system.gii.GiiModule',
            'password'=>'Enter Your Password Here',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters'=>array('127.0.0.1','::1'),
        ),
        */
    ),

    'theme' => 'bootstrap',

    // application components
    'components'=>array(
        'user'=>array(
            // enable cookie-based authentication
            'class' => 'WebUser',
            'allowAutoLogin'=>true,
            'loginUrl'=>array('index/index'),
            'returnUrl' => array('index/index')
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName'=>false,
            'rules'=>array(
                ''=>'index/index',
                //'personal/<controller:\w+>'=>'personal/<controller>/index',
                //array('api/tags/set', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
                //array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
                //array('api/update', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'PUT'),  // Update
                //array('api/delete', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
                //array('api/create', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'), // Create
            ),
        ),
        'bootstrap'=>array(
            'class'=>'bootstrap.components.Bootstrap',
        ),
        'errorHandler'=>array(
            // use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
                // uncomment the following to show log messages on web pages
                /*
                array(
                    'class'=>'CWebLogRoute',
                ),
                */
            ),
        )
    ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'=>array(
        // this is used in contact page
        'adminEmail'=>'fadfromdoost@yandex.ru',
        'smsApiUrl' => 'http://sms.ru/sms/send',
        'smsApiKey' => '61a669a3-6278-50a4-fd89-72dc20742dea',
        'myNumber' => '79200024144'
    ),
);