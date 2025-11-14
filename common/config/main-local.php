<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=postgres;dbname=loans',
            'username' => 'user',
            'password' => 'password',
            'charset' => 'utf8',
            // Включите schema caching для улучшения производительности
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            'schemaCache' => 'cache',
        ],
    ],
];