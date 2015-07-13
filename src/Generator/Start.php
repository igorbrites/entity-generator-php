<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\Database;

class Start
{
    public function createEntities()
    {
        $connection = Database::getInstance()->getConnection();

        $stmt = $connection->prepare('SHOW FULL TABLES WHERE Table_type != ?;');

        if (!$stmt->execute(['VIEW'])) {
            throw new \PDOException('Unable to get tables');
        }

        $tables = $stmt->fetchAll(\PDO::FETCH_NUM);

        echo "Preparing to generate " . count($tables) . " entities\n";

        foreach ($tables as $table) {
            $entity = (new Entity($table[0]))->generate();
            $entity->saveClassToFile();
            $entity->saveTestToFile();
        }
    }
}