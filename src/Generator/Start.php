<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\Database;

class Start
{
    public function createEntities()
    {
        $connection = Database::getInstance()->getConnection();

        $tables = $connection->query('SHOW TABLES', \PDO::FETCH_NUM);

        foreach ($tables as $table) {
            $entity = (new Entity($table[0]))->generate();
            $entity->saveToFile();
        }
    }
}