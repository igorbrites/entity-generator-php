<?php

namespace EntityGenerator\Generator;

class Connection
{
    /**
     * @var \PDO connection
     */
    private static $connection;

    /**
     * @param $config
     *
     * @return \PDO
     */
    public static function getInstance($config = [])
    {
        if (null === self::$connection) {
            try {
                self::$connection = new \PDO(
                    "mysql:dbname={$config['schema']};host={$config['host']}",
                    $config['user'],
                    $config['password']
                );
            } catch (\PDOException $e) {
                die("Error to connect to database: {$e->getMessage()}");
            }
        }

        return self::$connection;
    }
}