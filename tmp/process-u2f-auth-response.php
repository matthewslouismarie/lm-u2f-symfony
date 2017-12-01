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
    $server->setRegistrations(get_registrations_for_user($_POST['username'], $pdo))
       ->setSignRequests(unserialize($_SESSION[$_POST['auth-id']]['sign_requests']));
    $response = SignResponse::fromJson($_POST['response']);
    $registration = $server->authenticate($response);

    $u2f_authenticator_id = $_SESSION[$_POST['auth-id']]['u2f_authenticators'][$response->getClientData()->getChallenge()];

    // @todo sql transaction
    $stmt = $pdo->prepare('SELECT counter FROM u2f_authenticators WHERE id = ?;');
    $results = $stmt->execute(array($u2f_authenticator_id));
    $db_counter = $stmt->fetch()['counter']; // @todo error if more than two results?
    // echo '<pre>';
    // var_dump($db_counter);
    // echo '</pre>';
    
    // log in successful
    $stmt = $pdo->prepare('UPDATE u2f_authenticators SET counter = :counter WHERE id = :id;');
    $stmt->bindParam('counter', $response->getCounter());
    $stmt->bindParam('id', $u2f_authenticator_id);
    $stmt->execute();

    // (update Registration in storage with above)
    unset($_SESSION[$_POST['auth-id']]);
}