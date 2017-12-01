<?php

require_once __DIR__.'/../private/init.php';

use LM\Database\DatabaseConnection;

$db_credentials = json_decode(file_get_contents(__DIR__.'/../private/db.json'), true);

$db = DatabaseConnection::getInstance($db_credentials)->getPdo();

$db->exec(file_get_contents(__DIR__.'/../private/create_tables.sql'));