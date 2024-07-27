<?php
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');


return [
  'class' => 'yii\swiftmailer\Mailer',
  // send all mails to a file by default. You have to set
  // 'useFileTransport' to false and configure a transport
  // for the mailer to send real emails.
  // 'useFileTransport' => true,
  'transport' => [
    'class' => 'Swift_SmtpTransport',
    'host' => $_ENV['MAIL_HOST'],
    'username' => $_ENV['MAIL_USERNAME'],
    'password' => $_ENV['MAIL_PASSWORD'],
    'port' => $_ENV['MAIL_PORT'],
    'encryption' => 'tls',
  ],
];