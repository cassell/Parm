<?php

namespace Parm;

abstract class DataAccessObject extends DataArray
{
	const NEW_OBJECT_ID = -1;
	
	private $__modifiedColumns = array();

	abstract function getDatabaseName();

	abstract function getTableName();

	abstract function getIdField();

	abstract function getDefaultRow();
	
	abstract function getFactory();

	/**
	 * Constructor
     * @param array $row Array of data
	 * @return DataAccessObject Returns itself for chaining
     */
	function __construct($row = null)
	{
		if($row != null)
		{
			parent::__construct($row);
		}
		else
		{
			parent::__construct(static::getDefaultRow());
			$this->addModifiedColumn(static::getIdField());
			$this[static::getIdField()] = static::NEW_OBJECT_ID;
		}
	}
	
	protected function getFieldValue($fieldName)
	{
		if (array_key_exists($fieldName, $this))
		{
			return $this[$fieldName];
		}
		else
		{
			throw new \Parm\Exception\GetFieldValueException($fieldName . ' not initilized for get method in ' . get_class($this));
		}
	}
	
	
	protected function addModifiedColumn($column)
	{
		$this->__modifiedColumns[$column] = 1;
	}
	
	protected function clearModifiedColumns()
	{
		$this->__modifiedColumns[] = array();
	}
	
	
	/**
	 * Find an object by ID
     * @param integer $id ID of the row in the database
     * @return object|null The record from the database
     */
	static function findId($id)
	{
		$t = new static();
		return $t->getFactory()->getObject($id);
	}
	
	public function __clone() {
		
		$this[static::getIdField()] = static::NEW_OBJECT_ID;
		$this->clearModifiedColumns();
    }
	
	/**
     * Save the object to the database.
	 * @return object
     */
	function save()
	{
		$f = static::getFactory();
		
		$sql = array();
		
		foreach ($this->__modifiedColumns as $field => $j)
		{
			if ($field != $this->getIdField())
			{
				if ($this[$field] !== null)
				{
					$sql[] = $this->getTableName() . "." . $field . ' = "' . $f->escapeString($this[$field]) . '"';
				}
				else
				{
					$sql[] = ' ' . $this->getTableName() . "." . $field . ' = NULL';
				}
			}
		}
		
		if ($this->getId() != static::NEW_OBJECT_ID && count($sql) > 0)
		{
			$f->update('UPDATE ' . $this->getTableName() . " SET " . implode(",", $sql) . " WHERE " . $this->getTableName() . "." . $this->getIdField() . ' = ' . $this->getId());
		}
		else
		{
			if (count($sql) > 0)
			{
				$f->update('INSERT INTO ' . $this->getTableName() . " SET " . implode(",", $sql));
			}
			else
			{
				$f->update('INSERT INTO ' . $this->getTableName() . " VALUES()");
			}

			$this[$this->getIdField()] = $f->databaseNode->connection->insert_id;
		}
		
		$this->clearModifiedColumns();
		
		return $this;
	}

	/**
     * Delete the object from the database.
	 * @return TRUE if successful, False if failed or ID is not greater than
	 *  
     */
	function delete()
	{
		if((int)$this->getId() > 0)
		{
			$f = static::getFactory();
			$f->update("DELETE FROM " . $this->getTableName() . " WHERE " . $this->getIdField() . " = " . (int)$this->getId());
		}
	}

	/**
     * Get the ID (Primary Key) of the object
	 * @return integer ID of the record in the database. NEW_OBJECT_ID if a new record
     */
	function getId()
	{
		return $this->getFieldValue($this->getIdField());
	}
	
	/**
     * Test if the object has been saved to the database. Checks if the ID = NEW_OBJECT_ID (usually -1). Returns true if the object has never been saved to the database
	 * @return boolean
     */
	function isNewObject()
	{
		return ($this->getId() == self::NEW_OBJECT_ID);
	}
	

	/**
     * Convert to JSON ready array. The camel case fields and the primary key is always 'id'
	 * @return array
     */
	function toJSON()
	{
		$json = parent::toJSON();
		$json['id'] = $this->getId();
		return $json;
	}
	
	/**
     * Used by the generated classes
     */
	protected function setFieldValue($fieldName, $val)
	{
		if(strcmp($this[$fieldName], $val) !== 0)
		{
			$this->addModifiedColumn($fieldName);
		}
		$this[$fieldName] = $val;
	}

	/**
     * Used by the generated classes
     */
	protected function setDatetimeFieldValue($fieldName, $val)
	{
		if ($val != "" && $val != '')
		{
			if (is_integer($val))
			{
				$this->setFieldValue($fieldName, date($this->getFactory()->databaseNode->dateTimeStorageFormat, $val));
			}
			else
			{
				$this->setFieldValue($fieldName, $val);
			}
		}
		else
		{
			$this->setFieldValue($fieldName, NULL);
		}
	}

	/**
     * Used by the generated classes
     */
	protected function getDatetimeFieldValue($fieldName, $format = null)
	{
		if ($format == null)
		{
			return $this->getFieldValue($fieldName);
		}
		else
		{
			return ($this->getFieldValue($fieldName) ? date($format, strtotime($this->getFieldValue($fieldName))) : null);
		}
	}
	
}
