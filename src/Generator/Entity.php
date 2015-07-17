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
     * @var string classFile
     */
    private $classFile;

    /**
     * @var string testFile
     */
    private $testFile;

    /**
     * @var Field fields
     */
    private $fields;

    /**
     * @var array relations
     */
    private $relations = [];

    public function __construct($tableName)
    {
        parent::__construct();

        $this->tableName = $tableName;
        $this->name = String::convertToCamelCase($tableName);

        $this->classFile = '';
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
    public function getClassFile()
    {
        return $this->classFile;
    }

    /**
     * @param string $classFile
     *
     * @return Entity
     */
    public function setClassFile($classFile)
    {
        $this->classFile = $classFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getTestFile()
    {
        return $this->testFile;
    }

    /**
     * @param string $testFile
     */
    public function setTestFile($testFile)
    {
        $this->testFile = $testFile;
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
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param array $relations
     *
     * @return Entity
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function addRelation($field)
    {
        $this->relations[] = $field;

        return $this;
    }

    /**
     * @return Entity
     */
    public function generate()
    {
        echo "\nGenerating entity for table '{$this->tableName}'\n";

        $connection = Database::getInstance()->getConnection();

        $stmt = $connection->prepare(
            ' SELECT ' .
            '    c.COLUMN_NAME,' .
            '    c.COLUMN_DEFAULT,' .
            '    c.COLUMN_KEY,' .
            '    c.IS_NULLABLE,' .
            '    c.DATA_TYPE,' .
            '    c.COLUMN_TYPE,' .
            '    c.CHARACTER_MAXIMUM_LENGTH,' .
            '    GROUP_CONCAT(k.REFERENCED_TABLE_NAME) REFERENCED_TABLE_NAME,' .
            '    GROUP_CONCAT(k.REFERENCED_COLUMN_NAME) REFERENCED_COLUMN_NAME' .
            ' FROM' .
            '    information_schema.COLUMNS c' .
            '        LEFT JOIN' .
            '    information_schema.KEY_COLUMN_USAGE k' .
            '        ON c.TABLE_SCHEMA = k.TABLE_SCHEMA' .
            '        AND c.TABLE_NAME = k.TABLE_NAME' .
            '        AND c.COLUMN_NAME = k.COLUMN_NAME' .
            ' WHERE' .
            '    c.TABLE_SCHEMA = ? ' .
            '        AND c.TABLE_NAME = ? ' .
            ' GROUP BY ' .
            '    c.COLUMN_NAME,' .
            '    c.COLUMN_DEFAULT,' .
            '    c.COLUMN_KEY,' .
            '    c.IS_NULLABLE,' .
            '    c.DATA_TYPE,' .
            '    c.COLUMN_TYPE,' .
            '    c.CHARACTER_MAXIMUM_LENGTH;'
        );

        if (!$stmt->execute([Config::getinstance()->getDatabase()['schema'], $this->tableName])) {
            throw new \PDOException('No fields found on table ' . $this->tableName);
        }

        $fields = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($fields as $array) {
            //echo "Generating field '{$array['COLUMN_NAME']}' for table '{$this->tableName}'\n";

            $field = Field::createFromArray($array);
            $this->addField($field);

            if ($field->isForeignKey()) {
                $array['REFERENCED_TABLE_NAME'] = null;
                $this->addRelation($array['COLUMN_NAME']);
                $field = Field::createFromArray($array);
                $this->addField($field);
            }
        }

        return $this;
    }

    /**
     * @return Entity
     */
    public function renderClass()
    {
        echo "Rendering class '{$this->name}'\n";

        $template = $this->getTwig()->loadTemplate('entity.twig');
        $this->setClassFile($template->render(['entity' => $this, 'config' => Config::getinstance()]));

        return $this;
    }

    /**
     * @return Entity
     */
    public function renderTest()
    {
        echo "Rendering test class '{$this->name}Test'\n";

        $template = $this->getTwig()->loadTemplate('entityTest.twig');
        $this->setTestFile($template->render(['entity' => $this, 'config' => Config::getinstance()]));

        return $this;
    }

    /**
     * @return int
     */
    public function saveClassToFile()
    {
        if (empty($this->classFile)) {
            $this->renderClass();
        }

        $config = Config::getinstance();

        if (!file_exists($config->getOutputDir() . '/classes')) {
            mkdir($config->getOutputDir() . '/classes', 0777, true);
        }

        $path = $config->getOutputDir() . '/classes/' . $this->name . '.php';

        if (file_exists($path)) {
            $matches = [];
            $pattern = '/\/\/[\s]*region DONT REPLACE\n(.*)[\n]*\/\/[\s]*endregion/sm';
            $match = preg_match_all($pattern, file_get_contents($path), $matches);

            if ($match > 0) {
                $this->classFile = preg_replace($pattern, current($matches[0]), $this->classFile);
            }
        }

        echo "Saving class '{$this->name}' to path '{$path}'\n";

        return file_put_contents($path, $this->classFile);
    }

    /**
     * @return int
     */
    public function saveTestToFile()
    {
        if (!Config::getinstance()->isGenerateTests()) {
            return null;
        }

        if (empty($this->testFile)) {
            $this->renderTest();
        }

        $config = Config::getinstance();

        if (!file_exists($config->getOutputDir() . '/tests')) {
            mkdir($config->getOutputDir() . '/tests', 0777, true);
        }

        $path = $config->getOutputDir() . '/tests/' . $this->name . 'Test.php';

        echo "Saving test class '{$this->name}Test' to path '{$path}'\n";

        return file_put_contents($path, $this->testFile);
    }
}