<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            // 'class' => 'yii\caching\MemCache',
            // 'useMemcached' => true,
            // 'servers' => [
            //     [
            //         'host' => 'localhost',
            //         'port' => 11211,
            //     ]
            // ],
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ]
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'module/<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
            ]
        ],
        'assetManager' => [
            'forceCopy' => true,
            'bundles' => [
                'yii\widgets\PjaxAsset' => [
                    'basePath' => '@webroot',
                    'baseUrl' => '@web',
                    'js' => ['js/jquery.pjax.js'],
                    'depends' => ['yii\web\YiiAsset'],
                ],
            ],
        ],
    ],
];
