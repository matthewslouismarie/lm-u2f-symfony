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

function get_registrations_for_user(int $user_id, \PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT counter FROM member WHERE id = ?;');
    $stmt->execute(array($user_id));
    $results = $stmt->fetchAll();
    $registrations = array();
    foreach ($results as $counter) {
        $registration = new Registration();
        $registration->setCounter($counter);
        $registrations[] = $registration;
    }
    return $registrations;
}