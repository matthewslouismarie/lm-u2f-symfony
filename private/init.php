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

function get_registrations_for_user(int $member_id, \PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT counter FROM u2f_authenticators WHERE member_id = ?;');
    $stmt->execute(array($member_id));
    $results = $stmt->fetchAll();
    $registrations = array();
    foreach ($results as $counter) {
        $registration = new Registration();
        $registration->setCounter($counter);
        $registrations[] = $registration;
    }
    return $registrations;
}