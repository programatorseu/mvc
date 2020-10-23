<?php
return [
    'database' => [
        'db' => 'mvc',
        'user' => 'root',
        'pass' => '',
        'host' => 'mysql:host=127.0.0.1',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ]
];
