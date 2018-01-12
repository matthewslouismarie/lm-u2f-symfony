<?php

require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

DriverManager::getConnection(array(
    'driver' => 'pdo_sqlite',
));
