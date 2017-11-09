<?php

require_once 'src/functions.php';

spl_autoload_register(function ($class_name) {
    if ('Firehed' === substr($class_name, 0, 7)) {
        $filename = basename(str_replace('\\', '/', $class_name)).'.php';
        include 'src/'.$filename;
    }
});

spl_autoload_register(function ($class_name) {
    if ('LM' === substr($class_name, 0, 2)) {    
        include 'src/'.str_replace('\\', '/', $class_name).'.php';
    }
});


use Firehed\U2F\Server;
use LM\Database\DatabaseConnection;

$server = new Server();
$server->disableCAVerification()
       ->setAppId('https://shift-two.alwaysdata.net');

$request = json_encode($server->generateRegisterRequest()->jsonSerialize());

$db_credentials = array(
    'host' => 'mysql-shift-two.alwaysdata.net',
    'db' => 'shift-two_hp',
    'username' => '',
    'password' => 'Utilisateurs MySQL'
);
DatabaseConnection::getInstance($db_credentials);

require_once 'html.php';