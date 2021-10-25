<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->bootEnv(dirname(__DIR__) . '/.env', 'test');

// Set timezone for proper comparison of timestamps in the export.
date_default_timezone_set('Europe/Vienna');
