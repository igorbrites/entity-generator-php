<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\Config;
use EntityGenerator\Util\Database;
use EntityGenerator\Util\String;

class Entity extends Template
{
    /**
     * @var string tableName
     */
    private $tableName;

    /**
     * @var string name
     */
    private $name;

    /**
     * @var string file
     */
    private $file;

    /**
     * @var Field fields
     */
    private $fields;

    public function __construct($tableName)
    {
        parent::__construct();

        $this->tableName = $tableName;
        $this->name = String::convertToCamelCase($tableName);

        $this->file = '';
        $this->fields = [];
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     *
     * @return Entity
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Entity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return Entity
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return Field
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields
     *
     * @return Entity
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param Field $field
     *
     * @return Entity
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return Entity
     */
    public function generate()
    {
        $connection = Database::getInstance()->getConnection();

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

        if (!$stmt->execute([Config::getinstance()->getDatabase()['schema'], $this->tableName])) {
            throw new \PDOException('No fields found on table ' . $this->tableName);
        }

        $fields = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($fields as $array) {
            $field = Field::createFromArray($array);
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @return Entity
     */
    public function render()
    {
        $template = $this->getTwig()->loadTemplate('entity');
        $this->setFile($template->render(['entity' => $this]));

        return $this;
    }

    /**
     * @return int
     */
    public function saveToFile()
    {
        if (empty($this->file)) {
            $this->render();
        }

        $config = Config::getinstance();

        if (!file_exists($config->getOutputDir())) {
            mkdir($config->getOutputDir(), 0777, true);
        }

        return file_put_contents($config->getOutputDir() . '/' . $this->name . '.php', $this->file);
    }
}