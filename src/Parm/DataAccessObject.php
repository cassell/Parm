<?php

namespace Parm;

abstract class DataAccessObject extends DataAccessArray
{
	const NEW_OBJECT_ID = -1;
	protected $modifiedColumns;

	abstract function getDatabaseName();

	abstract function getTableName();

	abstract function getIdField();

	abstract function getDefaultRow();
	
	abstract function getFactory();

	/**
	 * Constructor
     * @param array $row Array of data
     */
	function __construct($row = null)
	{
		// setup $this->__data
		parent::__construct($row);

		// if $this->__data is null setup with defaults from table
		if ($this->__data == null)
		{
			$this->__data = static::getDefaultRow();
			if (static::getIdField() != null)
			{
				$this->__data[static::getIdField()] = self::NEW_OBJECT_ID;
				$this->__modifiedColumns[static::getIdField()] = 1;
			}
		}
		
		return $this;
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
	
	/**
     * Create a clone of the object in order to save a duplicate
	 * @return object|null A record that hasn't been save to the database
     */
	function createClone()
	{
		$obj = new static();

		// clone data
		$obj->__data = $this->__data;

		// set object_id to NEW_OBJECT_ID (-1)
		$obj->__data[static::getIdField()] = static::NEW_OBJECT_ID;

		// set all modified colums to 1
		$obj->__modifiedColumns = static::getDefaultRow();
		array_walk($obj->__modifiedColumns, function(&$v, $k){ $v = 1; });

		return $obj;
	}

	/**
     * Save the object to the database.
	 * @return object
     */
	function save()
	{
		$f = static::getFactory();

		if (!empty($this->__modifiedColumns))
		{
			foreach (array_keys($this->__modifiedColumns) as $field)
			{
				if ($field != $this->getIdField())
				{
					if ($this->__data[$field] !== null)
					{
						$sql[] = $this->getTableName() . "." . $field . ' = "' . $f->escapeString($this->__data[$field]) . '"';
					}
					else
					{
						$sql[] = ' ' . $this->getTableName() . "." . $field . ' = NULL';
					}
				}
			}

			if ($this->getId() != static::NEW_OBJECT_ID)
			{
				$f->update('UPDATE ' . $this->getTableName() . " SET " . implode(",", $sql) . " WHERE " . $this->getTableName() . "." . $this->getIdField() . ' = ' . $this->getId());
			}
			else
			{
				if($sql != null)
				{
					$sql = 'INSERT INTO ' . $this->getTableName() . " SET " . implode(",", $sql);
				}
				else
				{
					// empty object
					$sql = 'INSERT INTO ' . $this->getTableName() . " VALUES()";
				}

				$f->update($sql);

				if($f->databaseNode && $f->databaseNode->connection && $f->databaseNode->connection->insert_id > 0)
				{
					$this->__data[$this->getIdField()] = $f->databaseNode->connection->insert_id;
				}
				else
				{
					throw new \Parm\Exception\ErrorException("Insert failed: " . $sql);
				}
			}

			unset($this->__modifiedColumns);
		}
		
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
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
     * Get the ID (Primary Key) of the object
	 * @return integer ID of the record in the database, it will be -1 if a new record
     */
	function getId()
	{
		return $this->getFieldValue($this->getIdField());
	}
	
	/**
     * Test if the object has been saved to the database. Checks if the ID = NEW_OBJECT_ID (-1). Returns true if the object has never been saved to the database
	 * @return boolean
     */
	function isNewObject()
	{
		return ($this->getId() == self::NEW_OBJECT_ID);
	}
	

	/**
     * Convert to JSON ready array. The primary key is always 'id'
	 * @return array JSON ready with camel case fields and primary key is 'id'
     */
	function toJSON()
	{
		$j = array();
		if ($this->__data != null)
		{
			foreach ($this->__data as $field => $value)
			{
				if ($field == $this->getIdField())
				{
					$j['id'] = $value;
				}
				else
				{
					$j[static::columnToCamelCase($field)] = $value;
				}
			}
		}

		return $j;
	}
	
	protected function setFieldValue($fieldName, $val)
	{
		if (strcmp($this->__data[$fieldName], $val) !== 0)
		{
			$this->__modifiedColumns[$fieldName] = 1;
		}
		$this->__data[$fieldName] = $val;
	}

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
	
	
	/**
     * DEPRECATED: Get the Id (Primary Key) of the object
     */
	function getObjectId()
	{
		trigger_error('Get the getObjectId() has been deprecated in favor of getId()', E_USER_WARNING);
		return $this->getId();
	}
	
}

?>