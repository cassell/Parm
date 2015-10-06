<?php
namespace Parm\Generator;

class TableSchema
{
    /**
     * @var array
     */
    private $columns;
    private $idField;
    private $databaseName;
    private $tableName;

    public function __construct($databaseName, $tableName, $idField, $columns = [])
    {
        $this->databaseName = $databaseName;
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->idField = $idField;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return mixed
     */
    public function getIdField()
    {
        return $this->idField;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    public function isIdInteger()
    {
        return ($this->getPrimaryKeyColumn() && $this->getPrimaryKeyColumn()->isInteger());
    }

    public function isIdString()
    {
        return ($this->getPrimaryKeyColumn() && $this->getPrimaryKeyColumn()->isString());
    }

    public function isIdCalledId()
    {
        return ($this->getPrimaryKeyColumn() && strtolower($this->getPrimaryKeyColumn()->getColumnName()) ==  "id");
    }

    public function isIdAutoIncremented()
    {
        return ($this->getPrimaryKeyColumn() && $this->getPrimaryKeyColumn()->isAutoIncremented());
    }

    /**
     * @return null|Column
     */
    private function getPrimaryKeyColumn()
    {
        /** @var Column $col */
        foreach ($this->getColumns() as $col) {
            if ($col->isPrimaryKey()) {
                return $col;
            }
        }

        return null;

    }

}
