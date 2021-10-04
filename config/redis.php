<?php

return [

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'cache' => [
            'scheme' => env('REDIS_SCHEME', 'tls'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 1),
        ],

    ],

];