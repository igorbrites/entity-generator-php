<?php

namespace EntityGenerator\Util;

class Database
{
    /**
     * @var Database instance
     */
    private static $instance;

    /**
     * @var \PDO connection
     */
    private $connection;


    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        try {
            $config = Config::getinstance()->getDatabase();
            $this->connection = new \PDO(
                "mysql:dbname={$config['schema']};host={$config['host']}",
                $config['user'],
                $config['password']
            );
        } catch (\PDOException $e) {
            die("Error to connect to database: {$e->getMessage()}");
        }
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}