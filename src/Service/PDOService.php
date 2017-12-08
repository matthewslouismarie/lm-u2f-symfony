<?php

namespace App\Service;

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

    public function getPdo()
    {
        return $this->pdo;
    }
}