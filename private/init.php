<?php

require_once 'src/functions.php';

use Firehed\U2F\Registration;

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

/**
 * @todo replace user by member
 */
function get_registrations_for_user(string $username, \PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT counter, attestation, public_key, key_handle FROM u2f_authenticators WHERE member_id IN (SELECT id FROM members WHERE username = ?);');
    $stmt->execute(array($username));
    $results = $stmt->fetchAll();
    $registrations = array();
    foreach ($results as $counter) {
        $registration = new Registration();
        $registration->setCounter($counter['counter']);
        $registration->setAttestationCertificate($counter['attestation']);
        $registration->setPublicKey(base64_decode($counter['public_key']));
        $registration->setKeyHandle(base64_decode($counter['key_handle']));
        $registrations[] = $registration;
    }
    return $registrations;
}

/**
 * @todo make secure
 */
function generate_reg_id(): string
{
    $n = 0;
    do {
        $id = $n.'-reg-request';
        $n++;
    } while (isset($_SESSION[$id]));
    return $id;
}

/**
 * @todo make secure
 */
function generate_auth_id(): string
{
    $n = 0;
    do {
        $id = $n.'-auth-request';
        $n++;
    } while (isset($_SESSION[$id]));
    return $id;
}