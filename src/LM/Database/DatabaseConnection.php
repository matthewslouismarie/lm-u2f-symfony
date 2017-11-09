<?php

namespace LM\Database;

/**
 * Singleton class to get a database connection.
 * 
 * Use this class to get a database connection, then perform queries on it.
 * The class uses the information provided in ini/database.ini and ini/root.ini.
 * 
 * @author Louis-Marie Matthews
 */
class DatabaseConnection
{
    private $pdo;
    private static $instance = null;

    /**
    * Gets the database name, host and the root's username and password from ini files.
    */
    private function __construct(string $host, string $databaseName,
                                 string $username, string $password)
    {
        $hostLine = 'mysql:host='.$host.';';
        $db_line = 'dbname='.$databaseName.';';
        $charsetLine = 'charset=utf8';
        $userLine = $username;
        $passwordLine = $password;
        $additionalParameters = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
        $this->pdo = new \PDO($hostLine.$db_line.$charsetLine,
                             $userLine,
                             $passwordLine,
                             $additionalParameters);
        // TODO: remove array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    }

    public static function getInstance($credentials = null): DatabaseConnection
    {
        if (null === self::$instance) {
            self::$instance = new Databaseconnection(
                $credentials['host'],
                $credentials['db'],
                $credentials['username'],
                $credentials['password']
            );
        }
        return self::$instance;
    }

    public function __destruct()
    {
        // destroy the connection
        $this->pdo = null;
    }

    /**
    * This method performs a prepared statement on the database using the given prepared statement
    * and the values to feed it with.
    */
    public function query(string $preparedQuery, array $values)
    {
        $request = $this->pdo->prepare($preparedQuery);
        $success = $request->execute( $values );
        return $request;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}