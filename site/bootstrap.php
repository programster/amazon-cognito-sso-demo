<?php


require_once(__DIR__ . '/vendor/autoload.php');

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->overload('/.env');

$autoloader = new iRAP\Autoloader\Autoloader([
    __DIR__,
    __DIR__ . '/controllers',
    __DIR__ . '/exceptions',
    __DIR__ . '/libs',
    __DIR__ . '/middleware',
    __DIR__ . '/models',
    __DIR__ . '/views',
]);


define('SITE_NAME', $_ENV['SITE_NAME']);
define('AWS_REGION', $_ENV['AWS_REGION']);
define('AWS_KEY_ID', $_ENV['AWS_KEY_ID']);
define('AWS_KEY_SECRET', $_ENV['AWS_KEY_SECRET']);
define('COGNITO_HOSTED_UI_URL', $_ENV['COGNITO_HOSTED_UI_URL']);
define('COGNITO_CLIENT_ID', $_ENV['COGNITO_CLIENT_ID']);
define('COGNITO_CLIENT_SECRET', $_ENV['COGNITO_CLIENT_SECRET']);


