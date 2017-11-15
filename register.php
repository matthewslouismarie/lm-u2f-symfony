<?php

require 'private/init.php';

use Firehed\U2F\Server;
use LM\Database\DatabaseConnection;

$server = new Server();
$server->disableCAVerification()
       ->setAppId('https://shift-two.alwaysdata.net');

$request = json_encode($server->generateRegisterRequest()->jsonSerialize());

$db_credentials = json_decode(file_get_contents('private/db.json'), true);

$pdo = DatabaseConnection::getInstance($db_credentials)->getPdo();

$registrations = get_registrations_for_user(0, $pdo);
$sign_requests = json_encode($server->generateSignRequests($registrations));

require_once 'html.php';