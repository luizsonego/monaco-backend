<?php
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

return [
    'class' => 'yii\db\Connection',
    // 'dsn' => 'mysql:host=api-php-1:3306;dbname=monaco',
    'dsn' =>
        'mysql:host=' .
        $_ENV['DB_HOST'] .
        ':' .
        $_ENV['DB_PORT'] .
        ';dbname=' .
        $_ENV['DB_DATABASE'] .
        '',
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8',
];
