<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\String;

class Entity
{
    const INT         = 'int';
    const VARCHAR     = 'varchar';
    const DATETIME    = 'datetime';
    const DATE        = 'date';
    const TIMESTAMP   = 'timestamp';
    const ENUM        = 'enum';
    const FLOAT       = 'float';
    const TEXT        = 'text';
    const FOREIGN_KEY = 'fk';

    private $tableName;

    private $schema;

    private $entityName;

    private $file;

    private $setters;

    private $getters;

    public function __construct($tableName, $schema)
    {
        $this->tableName = $tableName;
        $this->schema = $schema;
        $this->entityName = String::convertToCamelCase($tableName);

        $this->file = '';
        $this->setters = [];
        $this->getters = [];
    }

    public function generate()
    {
        $connection = Connection::getInstance();

        $stmt = $connection->prepare(
            ' SELECT ' .
            '    c.COLUMN_NAME,' .
            '    c.COLUMN_DEFAULT,' .
            '    c.IS_NULLABLE,' .
            '    c.DATA_TYPE,' .
            '    c.COLUMN_TYPE,' .
            '    c.CHARACTER_MAXIMUM_LENGTH,' .
            '    k.REFERENCED_TABLE_NAME,' .
            '    k.REFERENCED_COLUMN_NAME' .
            ' FROM ' .
            '    information_schema.COLUMNS c' .
            '        LEFT JOIN' .
            '    information_schema.KEY_COLUMN_USAGE k ON c.TABLE_SCHEMA = k.TABLE_SCHEMA' .
            '        AND c.TABLE_NAME = k.TABLE_NAME' .
            '        AND c.COLUMN_NAME = k.COLUMN_NAME' .
            ' WHERE ' .
            '    c.TABLE_SCHEMA = ?' .
            '    AND c.TABLE_NAME = ?;'
        );

        if (!$stmt->execute([$this->schema, $this->tableName])) {
            throw new \PDOException('Any fields found on table ' . $this->tableName);
        }

        $fields = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($fields as $field) {
            $fieldName = $field['COLUMN_NAME'];
            $defaultValue = $field['COLUMN_DEFAULT'];
            $isNullable = $field['IS_NULLABLE'] === 'YES';
            $fieldDataType = $field['DATA_TYPE'];
            $fieldType = $field['COLUMN_TYPE'];
            $maxLength = $field['CHARACTER_MAXIMUM_LENGTH'];
            $referencedEntity = $field['REFERENCED_TABLE_NAME'];

            $field = (new Field())
                ->setName($fieldName)
                ->setDefault($defaultValue)
                ->setNullable($isNullable)
                ->setType($fieldDataType)
                ->setMaxLength($maxLength)
            ;



        }
    }

    public function saveToFile()
    {

    }

    public function setMethod($field)
    {

    }

    public function getMethod($field)
    {

    }
}