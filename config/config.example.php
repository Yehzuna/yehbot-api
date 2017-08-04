<?php

return [
    'debug'  => true,
    'env'    => 'dev',
    'key'    => '',

    'database'     => [
        'adapter'  => 'mysql',
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname'   => 'api',
    ],

    'twitch' => [
        'client_id'     => '123456',
        'client_secret' => '123456',
        'redirect_uri'  => 'http://localhost/',
    ],

    'bot' => [
        'client_id'     => '123456',
    ],

    'dashboard' => [
        'client_id'     => '123456',
    ],
];
