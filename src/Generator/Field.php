<?php

namespace EntityGenerator\Generator;

use EntityGenerator\Util\String;

class Field extends Template
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

    /**
     * @var string name
     */
    private $name;

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
     * @var string foreignKey
     */
    private $foreignKey;

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
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param string $foreignKey
     *
     * @return Field
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function getGetter()
    {
        $template = $this->getTwig()->loadTemplate('getter');
        return $template->render(['field' => $this]);
    }

    public function getSetter()
    {
        $template = $this->getTwig()->loadTemplate('setter');
        return $template->render(['field' => $this]);
    }

    public function getAttribute()
    {
        $template = $this->getTwig()->loadTemplate('attribute');
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
            ->setName($fieldName)
            ->setDefault($defaultValue)
            ->setNullable($isNullable)
            ->setType(is_null($referencedEntity) ? $fieldDataType : Field::FOREIGN_KEY)
            ->setMaxLength($maxLength)
        ;

        if (!is_null($referencedEntity)) {
            $field->setForeignKey($referencedEntity);
        }

        if (Field::ENUM === $fieldDataType) {
            $field->setOptions(String::convertEnumToArray($fieldType));
        }

        return $field;
    }
}