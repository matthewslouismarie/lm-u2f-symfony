<?php

session_start();

require 'private/init.php';

use Firehed\U2F\Server;
use LM\Database\DatabaseConnection;
use Firehed\U2F\RegisterResponse;
use Firehed\U2F\SignResponse;

$server = new Server();
$server->disableCAVerification()
       ->setAppId('https://shift-two.alwaysdata.net');

$db_credentials = json_decode(file_get_contents('private/db.json'), true);
$pdo = DatabaseConnection::getInstance($db_credentials)->getPdo();

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $auth_id = generate_auth_id();

    $registrations = get_registrations_for_user($_POST['username'], $pdo);
    $sign_requests = $server->generateSignRequests($registrations, $auth_id);

    var_dump($sign_requests[0]->getChallenge());
    

    $_SESSION[$auth_id]['sign_requests'] = serialize($sign_requests);
    require_once 'u2flogin.html.php';
}