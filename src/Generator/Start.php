<?php

namespace EntityGenerator\Generator;

class Start
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        isset($this->config['namespace']) || ($this->config['namespace'] = '');
    }

    public function createEntities()
    {
        $connection = Connection::getInstance($this->config['database']);

        $tables = $connection->query('SHOW TABLES', \PDO::FETCH_NUM);

        foreach ($tables as $table) {
            $entity = new Entity($table[0], $this->config['database']['schema']);
            $entity->generate();
            $entity->saveToFile();
        }
    }
}