<?php

require_once 'src/functions.php';

spl_autoload_register(function ($class_name) {
    $filename = basename(str_replace('\\', '/', $class_name)).'.php';
    include 'src/'.$filename;
});


use Firehed\U2F\Server;

$server = new Server();
$server->disableCAVerification()
       ->setAppId('https://shift-two.alwaysdata.net');

$request = json_encode($server->generateRegisterRequest()->jsonSerialize());

require_once 'html.php';