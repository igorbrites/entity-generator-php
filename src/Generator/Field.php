<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\Config;
use EntityGenerator\Util\String;

class Field extends Template
{
    const INT       = 'int';
    const VARCHAR   = 'varchar';
    const DATETIME  = 'datetime';
    const DATE      = 'date';
    const TIMESTAMP = 'timestamp';
    const ENUM      = 'enum';
    const FLOAT     = 'float';
    const TEXT      = 'text';
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
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return Field
     */
    public function setDefault($default)
    {
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
        if (in_array($type, [self::VARCHAR, self::TEXT])) {
            $type = self::STRING;
        } elseif (in_array($type, [self::DATETIME, self::DATE, self::TIMESTAMP])) {
            $type = Config::getinstance()->getDateType();
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
     * @return string
     */
    public function getUcName()
    {
        return $this->ucName;
    }

    public function isTyped()
    {
        return in_array($this->type, [self::DATE, self::DATETIME, self::TIMESTAMP]) ||
            $this->isForeignKey();
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
        $isNullable = $array['IS_NULLABLE'] === 'YES';
        $fieldDataType = $array['DATA_TYPE'];
        $fieldType = $array['COLUMN_TYPE'];
        $maxLength = $array['CHARACTER_MAXIMUM_LENGTH'];
        $referencedEntity = $array['REFERENCED_TABLE_NAME'];

        /** @var Field $field */
        $field = (new self())
            ->setName(String::convertToCamelCase($fieldName, true))
            ->setDefault($defaultValue)
            ->setNullable($isNullable)
            ->setMaxLength($maxLength)
            ->setType($fieldDataType);

        if (!is_null($referencedEntity)) {
            $field
                ->setName(String::convertToCamelCase($referencedEntity, true))
                ->setType(String::convertToCamelCase($referencedEntity))
                ->setForeignKey(true);
        }

        if (Field::ENUM === $fieldDataType) {
            $field->setOptions(String::convertEnumToArray($fieldType));
        }

        return $field;
    }
}
