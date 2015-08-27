<?php

namespace Parm;

use Parm\Exception\GetFieldValueException;

abstract class DataAccessObject extends Row implements TableInterface
{
    private $__modifiedColumns = array();

    /**
     * Constructor
     * @param array $row Array of data
     */
    public function __construct(array $row = null)
    {
        if ($row == null) {
            $row = static::getDefaultRow();
        }

        if (!array_key_exists(static::getIdField(), $row)) {
            $row[static::getIdField()] = null;
        }

        parent::__construct($row);

        if ($this->isNewObject()) {
            foreach (array_keys($row) as $field) {
                if (in_array($field, static::getFields())) {
                    $this->addModifiedColumn($field);
                }
            }
        }
    }

    /**
     * @param string $column the name of the column
     */
    protected function addModifiedColumn($column)
    {
        $this->__modifiedColumns[$column] = 1;
    }

    /**
     * clear the list of modified columns so none are saved to the database
     */
    protected function clearModifiedColumns()
    {
        $this->__modifiedColumns = array();
    }

    /**
     * Find an object by ID
     * @param  integer                 $id      ID of the row in the database
     * @param  DataAccessObjectFactory $factory
     * @return null|object             The record from the database
     */
    public static function findId($id, DataAccessObjectFactory $factory = null)
    {
        $factory = self::ifNullReturnNewFactory($factory);

        return $factory->findId($id);
    }

    /**
     * Save the object to the database.
     * @return object
     */
    public function save()
    {
        $factory = static::getFactory();

        $sql = array();

        foreach ($this->__modifiedColumns as $field => $j) {
            if ($field != $this->getIdField() && in_array($field, static::getFields())) {
                if ($this[$field] !== null) {
                    $sql[] = $this->getTableName() . "." . $field . ' = ' . $factory->escapeString($this[$field]) . '';
                } else {
                    $sql[] = $this->getTableName() . "." . $field . ' = NULL';
                }
            }
        }

        if ($this->isNewObject()) {
            if (count($sql) > 0) {
                $factory->update('INSERT INTO ' . $this->getTableName() . " SET " . implode(",", $sql));
            } else {
                $factory->update('INSERT INTO ' . $this->getTableName() . " VALUES()");
            }

            $this[$this->getIdField()] = $factory->getLastInsertId();
        } elseif (count($sql) > 0) {
            $factory->update('UPDATE ' . $this->getTableName() . " SET " . implode(",", $sql) . " WHERE " . $this->getTableName() . "." . $this->getIdField() . ' = ' . $this->getId());
        }

        $this->clearModifiedColumns();

        return $this;
    }

    /**
     * Delete the object from the database.
     * @return TRUE if successful, False if failed or ID is not greater than
     *
     * @throws Exception\RecordNotFoundException
     * @throws Exception\UpdateFailedException
     */
    public function delete()
    {
        if (!$this->isNewObject()) {
            $f = static::getFactory();
            $f->update("DELETE FROM " . $this->getTableName() . " WHERE " . $this->getIdField() . " = " . (int) $this->getId());
        } else {
            throw new \Parm\Exception\RecordNotFoundException("delete() failed: You can't delete this object from the database as it hasn't been saved yet.");
        }
    }

    /**
     * Get the ID (Primary Key) of the object
     * @return mixed ID of the record in the database. null if a new record
     */
    public function getId()
    {
        return $this->getFieldValue($this->getIdField());
    }

    /**
     * Test if the object has been saved to the database. The primary key will be null
     * @return boolean
     */
    public function isNewObject()
    {
        return $this->getId() == null;
    }

    /**
     * Clone the object as a new object. This will cause the save() to save a new row to the database.
     * @return DataAccessObject
     */
    public function duplicateAsNewObject()
    {
        $data = (array) $this;
        if (static::getIdField()) {
            unset($data[static::getIdField()]);
        }

        return new static($data);
    }

    /**
     * Convert to JSON ready array. Camel case fields and the primary key is always mapped to 'id'
     * @return array
     */
    public function toJSON()
    {
        $json = parent::toJSON();
        $json['id'] = $this->getId();

        return $json;
    }

    /**
     * Used by the generated classes
     */

    public function getFieldValue($columnName)
    {
        if (array_key_exists($columnName, $this)) {
            return $this[$columnName];
        } else {
            throw new GetFieldValueException($columnName . ' not initialized for get method in ' . get_class($this));
        }
    }

    /**
     * @param $columnName
     * @param $val
     * @return $this
     */
    protected function setFieldValue($columnName, $val)
    {
        if ($val === NULL || strcmp($this[$columnName], $val) !== 0) {
            $this->addModifiedColumn($columnName);
            $this[$columnName] = $val;
        }

        return $this;
    }

    /**
     * @param $columnName
     * @param $val
     * @return DataAccessObject
     */
    protected function setIntFieldValue($columnName, $val)
    {
        if ($val === null) {
            return $this->setFieldValue($columnName, NULL);
        } else {
            return $this->setFieldValue($columnName, (int) $val);
        }
    }

    /**
     * @param $columnName
     * @return int|null
     * @throws GetFieldValueException
     */
    protected function getIntFieldValue($columnName)
    {
        $val = $this->getFieldValue($columnName);
        if ($val === null) {
            return null;
        } else {
            return (int) $val;
        }
    }

    /**
     * @param $columnName
     * @param $mixed
     * @return $this|DataAccessObject
     */
    protected function setDateFieldValue($columnName, $mixed)
    {
        if ($mixed instanceof \DateTime) {
            return $this->setFieldValue($columnName, $mixed->format($this->getFactory()->getDateStorageFormat()));
        } elseif (is_int($mixed)) {
            $date = new \DateTime();
            $date->setTimestamp($mixed);

            return $this->setFieldValue($columnName, $date->format($this->getFactory()->getDateStorageFormat()));
        } else {
            return $this->setFieldValue($columnName, $mixed);
        }
    }

    /**
     * @param $columnName
     * @param null $format
     * @return mixed|string
     * @throws GetFieldValueException
     */
    protected function getDateFieldValue($columnName, $format = null)
    {
        if ($format != null && $this->getFieldValue($columnName) != null) {
            // \Datetime::createFromFormat parses a date value format and sets the time of day to the current system time
            // see http://php.net/manual/en/datetime.createfromformat.php for explanation
            $dateTime = \DateTime::createFromFormat($this->getFactory()->getDateStorageFormat(), $this->getFieldValue($columnName));

            if ($dateTime) { // $dateTime will be a new DateTime instance or FALSE on failure.
                // setting the time to midnight as the expected value when pulling from a database
                $dateTime->setTime(0, 0, 0);

                return $dateTime->format($format);
            }
        }

        return $this->getFieldValue($columnName);
    }

    /**
     * @param $columnName
     * @param $format
     * @return \DateTime|null
     * @throws GetFieldValueException
     */
    protected function getDatetimeObjectFromField($columnName, $format)
    {
        $val = $this->getFieldValue($columnName);
        if ($val != null && $format != null) {
            return \DateTime::createFromFormat($format, $val);
        } else {
            return null;
        }
    }

    /**
     * @param $columnName
     * @param $mixed
     * @return $this|DataAccessObject
     */
    protected function setDatetimeFieldValue($columnName, $mixed)
    {
        if ($mixed instanceof \DateTime) {
            return $this->setFieldValue($columnName, $mixed->format($this->getFactory()->getDatetimeStorageFormat()));
        } elseif (is_int($mixed)) {
            $date = new \DateTime();
            $date->setTimestamp($mixed);

            return $this->setFieldValue($columnName, $date->format($this->getFactory()->getDatetimeStorageFormat()));
        } else {
            return $this->setFieldValue($columnName, $mixed);
        }
    }

    /**
     * @param $columnName
     * @param null $format
     * @return mixed|string
     * @throws GetFieldValueException
     */
    protected function getDatetimeFieldValue($columnName, $format = null)
    {
        if ($format != null && $this->getFieldValue($columnName) != null) {
            $dateTime = \DateTime::createFromFormat($this->getFactory()->getDatetimeStorageFormat(), $this->getFieldValue($columnName));

            if ($dateTime) { // $dateTime will be a new DateTime instance or FALSE on failure.

                return $dateTime->format($format);
            }
        }

        return $this->getFieldValue($columnName);
    }

    /**
     * @param $columnName
     * @param $val
     * @return $this|DataAccessObject
     */
    protected function setBooleanFieldValue($columnName, $val)
    {
        if ($val === null) {
            return $this->setFieldValue($columnName, NULL);
        } else {
            return $this->setFieldValue($columnName, $val ? 1 : 0);
        }
    }

    /**
     * @param $columnName
     * @return bool|null
     * @throws GetFieldValueException
     */
    protected function getBooleanFieldValue($columnName)
    {
        $val = $this->getFieldValue($columnName);
        if ($val === null) {
            return null;
        } else {
            return (bool) $val;
        }
    }

    /**
     * @param $columnName
     * @param $val
     * @return DataAccessObject
     */
    protected function setNumericalFieldValue($columnName, $val)
    {
        if ($val == null) {
            return $this->setFieldValue($columnName, NULL);
        } else {
            return $this->setFieldValue($columnName, (float) $val);
        }
    }

    /**
     * @param $columnName
     * @return float|null
     * @throws GetFieldValueException
     */
    protected function getNumericalFieldValue($columnName)
    {
        $val = $this->getFieldValue($columnName);
        if ($val == null) {
            return null;
        } else {
            return (float) $val;
        }
    }

    /**
     * @param DataAccessObjectFactory|null $factory
     * @return DataAccessObjectFactory
     */
    private function ifNullReturnNewFactory(DataAccessObjectFactory $factory = null)
    {
        if ($factory == null) {
            return static::getFactory();
        } else {
            return $factory;
        }
    }

}
