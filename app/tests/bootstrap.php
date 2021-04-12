<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:drop --env='.$_SERVER['APP_ENV'].' --force --if-exists --no-interaction',
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:create --env='.$_SERVER['APP_ENV'],
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:schema:update --env='.$_SERVER['APP_ENV'].' --force --no-interaction',
    __DIR__
));
