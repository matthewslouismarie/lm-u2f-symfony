<?php

session_start();

require 'private/init.php';

use Firehed\U2F\Server;
use LM\Database\DatabaseConnection;
use Firehed\U2F\RegisterResponse;

$server = new Server();
$server->disableCAVerification()
       ->setAppId('https://shift-two.alwaysdata.net');

if ('GET' === $_SERVER['REQUEST_METHOD']) {
    $request = $server->generateRegisterRequest();
    $_SESSION['request'] = serialize($request); // TODO
    $request_json = json_encode($request); // ->jsonSerialize()?

    $db_credentials = json_decode(file_get_contents('private/db.json'), true);

    $pdo = DatabaseConnection::getInstance($db_credentials)->getPdo();

    $registrations = get_registrations_for_user(0, $pdo);
    $sign_requests = json_encode($server->generateSignRequests($registrations));
    require_once 'html.php';
} elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
    $request = unserialize($_SESSION['request']);
    $username = $_POST['username'];
    $server->setRegisterRequest($request);
    $response = RegisterResponse::fromJson($_POST['challenge']);
    $registration = $server->register($response);

    $db_credentials = json_decode(file_get_contents('private/db.json'), true);

    $pdo = DatabaseConnection::getInstance($db_credentials)->getPdo();

    $pdo->beginTransaction();
    $members_insert = $pdo->prepare('INSERT INTO members VALUES (NULL, :username)');
    $members_insert->bindParam(':username', $username);
    $success = $members_insert->execute();
    $u2f_authenticators_insert = $pdo->prepare('INSERT INTO u2f_authenticators VALUES (NULL, :member_id, :counter, :attestation, :public_key, :key_handle)');
    $u2f_authenticators_insert->bindParam(':member_id', $pdo->lastInsertId());
    $u2f_authenticators_insert->bindParam(':attestation', $registration->getAttestationCertificateBinary());
    $u2f_authenticators_insert->bindParam(':counter', $registration->getCounter());
    $u2f_authenticators_insert->bindParam(':public_key', $registration->getPublicKey());
    $u2f_authenticators_insert->bindParam(':key_handle', $registration->getKeyHandleBinary());
    $u2f_authenticators_insert->execute();
    $pdo->commit();
    echo '<pre>';
    var_dump($registration);
    echo '</pre>';
}