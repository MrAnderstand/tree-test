<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
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
    'modules' => [
        'treemanager' =>  [
            'class' => '\kartik\tree\Module',
            // other module settings, refer detailed documentation
        ]
    ],
];
