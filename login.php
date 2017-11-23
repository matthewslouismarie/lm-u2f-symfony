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

if ('GET' === $_SERVER['REQUEST_METHOD']) {
    $registrations = get_registrations_for_user(2, $pdo);
    $sign_requests = $server->generateSignRequests($registrations);

    $_SESSION['registrations'] = serialize($registrations);
    $_SESSION['sign_requests'] = serialize($sign_requests);
    require_once 'login.html.php';
} elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
    $server->setRegistrations(get_registrations_for_user(2, $pdo))
       ->setSignRequests(unserialize($_SESSION['sign_requests']));
$response = SignResponse::fromJson($_POST['response']);
$registration = $server->authenticate($response);
// (update Registration in storage with above)
}