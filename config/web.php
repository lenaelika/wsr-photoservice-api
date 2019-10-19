<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@upload' => '@app/../photos', // xxx-m2/photos
    ],
    'components' => [
        'request' => [
            'baseUrl' => '/api',
            'parsers' => [
                'application/json' => yii\web\JsonParser::class, 
            ],
            'cookieValidationKey' => 'ALJKtzZN3ZNYRZaW2nzrhg4dlXYlnRc2',
            'enableCsrfValidation' => false,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
            // 'targets' => [
            //     [
            //         'class' => 'yii\log\FileTarget',
            //         'categories' => [
            //             'yii\db\*',
            //         ],
            //         'levels' => ['profile'],
            //         'logFile' => '@runtime/logs/queries.txt',
            //     ],
            // ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'photo',
                    'pluralize' => false,
                    'patterns' => [
                        'POST' => 'create',
                        'POST {id}' => 'patch',
                        'PATCH {id}' => 'update',
                        'GET' => 'index',
                        'GET {id}' => 'view',
                        'DELETE {id}' => 'delete',
                    ],
                ], [
                    'pattern' => 'user/<id>/share',
                    'route' => 'user/share',
                    'verb' => 'POST'
                ], [
                    'pattern' => 'user',
                    'route' => 'user/search',
                    'verb' => 'GET',
                ], [
                    'pattern' => '<action:(signup|login|logout)>',
                    'route' => 'user/<action>',
                    'verb' => 'POST',
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
