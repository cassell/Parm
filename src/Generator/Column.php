<?php
namespace Parm\Generator;

class Column
{
    private $columnName;
    private $type;
    /**
     * @var bool
     */
    private $primaryKey;
    private $tableName;
    /**
     * @var null
     */
    private $defaultValue;
    private $extra;

    public function __construct($tableName, $columnName, $type, $extra, $defaultValue, $primaryKey = false)
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->type = $type;
        $this->extra = $extra;
        $this->primaryKey = $primaryKey;
        $this->defaultValue = $defaultValue;

        // @codeCoverageIgnoreStart
        if (strtolower($this->columnName) == "id" && !$this->primaryKey) {
            throw new \Parm\Exception\ErrorException($tableName . '.' .$columnName . ' ('. $type .') is an invalid column name unless it is the primary key for the ' . $columnName . '. It causes a collision with the function getId() which always returns the primary key.');
        }
        // @codeCoverageIgnoreEnd

    }

    /**
     * @return mixed
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function isPrimaryKey()
    {
        return ($this->primaryKey === true);
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function isInteger()
    {
        return !$this->isBoolean() && (preg_match("/int\\(/", $this->getType()));
    }

    public function isTypeDate()
    {
        return ($this->getType() == "date");
    }

    public function isBoolean()
    {
        return ($this->getType() == "tinyint(1)" || $this->getType() == "int(1)") ;
    }

    public function isTypeDateTime()
    {
        return ($this->getType() == "datetime" || $this->getType() == "timestamp");
    }

    public function isNumeric()
    {
        return (preg_match("/decimal/", $this->getType()) || preg_match("/float/", $this->getType()) || preg_match("/double/", $this->getType()) || preg_match("/real/", $this->getType()));
    }

    public function isString()
    {
        return ( !$this->isInteger() &&
                 !$this->isTypeDate() &&
                 !$this->isBoolean() &&
                 !$this->isTypeDateTime() &&
                 !$this->isNumeric());
    }

    public function isAutoIncremented()
    {
        return ($this->extra == "auto_increment");
    }


}