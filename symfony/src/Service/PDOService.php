<?php

namespace App\Service;

use Firehed\U2F\Registration;

class PDOService
{
    private $pdo;

    /**
     * @todo refactor (code to create pdo should be isolated from code that
     * reads json file)
     * @todo remove array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
     */
    public function __construct()
    {
        $config = json_decode(file_get_contents(__DIR__.'/../db.json'), true);
        
        $hostLine = 'mysql:host='.$config['host'].';';
		$databaseNameLine = 'dbname='.$config['db'].';';
		$charsetLine = 'charset=utf8';
		$userLine = $config['username'];
		$passwordLine = $config['password'];
		$additionalParameters = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
		$this->pdo = new \PDO($hostLine.$databaseNameLine.$charsetLine,
			                 $userLine,
			                 $passwordLine,
			                 $additionalParameters);
    }

    /**
     * @todo Pdo or PDO?
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @todo replace user by member
     * @todo temp location
     */
    public function getRegistrationsForUser(string $username): array
    {
        $stmt = $this->pdo->prepare('SELECT id, counter, attestation, public_key, key_handle FROM u2f_authenticators WHERE member_id IN (SELECT id FROM members WHERE username = ?);');
        $stmt->execute(array($username));
        $results = $stmt->fetchAll();
        $registrations = array();
        foreach ($results as $row) {
            $registration = new Registration($row['id']);
            $registration->setCounter($row['counter']);
            $registration->setAttestationCertificate($row['attestation']);
            $registration->setPublicKey(base64_decode($row['public_key']));
            $registration->setKeyHandle(base64_decode($row['key_handle']));
            $registrations[$row['id']] = $registration;
        }
        return $registrations;
    }
}