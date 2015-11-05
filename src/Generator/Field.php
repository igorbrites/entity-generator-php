<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\Config;
use EntityGenerator\Util\String;

class Field extends Template
{
    const INT       = 'int';
    const BIGINT    = 'bigint';
    const TINYINT   = 'tinyint';

    const CHAR      = 'char';
    const VARCHAR   = 'varchar';
    const ENUM      = 'enum';
    const TEXT      = 'text';

    const DATETIME  = 'datetime';
    const DATE      = 'date';
    const TIMESTAMP = 'timestamp';
    const TIME      = 'time';

    const FLOAT     = 'float';
    const STRING    = 'string';

    /**
     * @var string name
     */
    private $name;

    /**
     * @var string ucName
     */
    private $ucName;

    /**
     * @var mixed default
     */
    private $default;

    /**
     * @var bool nullable
     */
    private $nullable;

    /**
     * @var string type
     */
    private $type;

    /**
     * @var array options
     */
    private $options;

    /**
     * @var int maxLength
     */
    private $maxLength;

    /**
     * @var bool foreignKey
     */
    private $foreignKey = false;

    /**
     * @var bool primaryKey
     */
    private $primaryKey = false;

    /**
     * @var bool date
     */
    private $date = false;

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
     * @return Field
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->ucName = ucfirst($name);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        $default = $this->default;

        if (in_array($this->type, [self::STRING, self::ENUM, self::TIME])) {
            $default = "'$default'";
        }

        return $default;
    }

    /**
     * @param mixed $default
     *
     * @return Field
     */
    public function setDefault($default)
    {
        if ($this->isTyped()) {
            $default = null;
        }

        $this->default = $default;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @param boolean $nullable
     *
     * @return Field
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Field
     */
    public function setType($type)
    {
        if (in_array($type, [self::VARCHAR, self::TEXT, self::CHAR, self::TIME])) {
            $type = self::STRING;
            $this->setPrimaryKey(false);
        } elseif (in_array($type, [self::DATETIME, self::DATE, self::TIMESTAMP])) {
            $type = Config::getinstance()->getDateType();
            $this->setDate(true);
        } elseif (in_array($type, [self::INT, self::TINYINT, self::BIGINT])) {
            $type = self::INT;
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return Field
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength
     *
     * @return Field
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param bool $foreignKey
     *
     * @return Field
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param boolean $primaryKey
     *
     * @return Field
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDate()
    {
        return $this->date;
    }

    /**
     * @param boolean $date
     *
     * @return Field
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getUcName()
    {
        return $this->ucName;
    }

    public function getProvider()
    {
        if ($this->isForeignKey()) {
            return null;
        }

        if ($this->isPrimaryKey()) {
            return 'providerPrimaryKey';
        }

        $provider = '';

        switch ($this->type) {
            case self::ENUM:
                $provider = 'Enum';
                break;

            case self::STRING:
                $provider = 'String';
                break;

            case self::FLOAT:
                $provider = 'Float';
                break;

            case self::INT:
                $provider = 'Integer';
                break;
        }

        if (empty($provider)) {
            return null;
        }

        $provider .= $this->isNullable() ? 'Nullable' : 'NotNull';

        return 'provider' . $provider;
    }

    public function isTyped()
    {
        return $this->getType() === Config::getinstance()->getDateType() || $this->isForeignKey();
    }

    public function isCasted()
    {
        return in_array($this->type, [self::STRING, self::FLOAT, self::INT]);
    }

    public function getGetter()
    {
        $template = $this->getTwig()->loadTemplate('getter.twig');

        return $template->render(['field' => $this]);
    }

    public function getSetter()
    {
        $template = $this->getTwig()->loadTemplate('setter.twig');

        return $template->render(['field' => $this]);
    }

    public function getAttribute()
    {
        $template = $this->getTwig()->loadTemplate('attribute.twig');

        return $template->render(['field' => $this]);
    }

    /**
     * @param array $array
     *
     * @return Field
     */
    public static function createFromArray(array $array)
    {
        $fieldName = $array['COLUMN_NAME'];
        $defaultValue = $array['COLUMN_DEFAULT'];
        $isPrimaryKey = $array['COLUMN_KEY'] === 'PRI';
        $isNullable = $array['IS_NULLABLE'] === 'YES';
        $fieldDataType = $array['DATA_TYPE'];
        $fieldType = $array['COLUMN_TYPE'];
        $maxLength = $array['CHARACTER_MAXIMUM_LENGTH'];
        $referencedEntity = $array['REFERENCED_TABLE_NAME'];

        /** @var Field $field */
        $field = (new self())
            ->setName(String::convertToCamelCase($fieldName, true))
            ->setNullable($isNullable)
            ->setMaxLength($maxLength)
            ->setPrimaryKey($isPrimaryKey)
            ->setType($fieldDataType)
            ->setDefault($defaultValue);

        if (!is_null($referencedEntity)) {
            $fkName = String::convertForeignKeyName($fieldName);

            $field
                ->setName(String::convertToCamelCase($fkName, true) . 'Entity')
                ->setType(String::convertToCamelCase($referencedEntity))
                ->setForeignKey(true)
                ->setPrimaryKey(false)
                ->setDefault(null);
        }

        if (Field::ENUM === $fieldDataType) {
            $field->setOptions(String::convertEnumToArray($fieldType));
        }

        return $field;
    }
}
