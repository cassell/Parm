<?php

namespace Parm;

abstract class DataAccessObject extends DataArray implements TableInterface
{
	private $__modifiedColumns = array();

	/**
	 * Constructor
     * @param array $row Array of data
     */
	function __construct(Array $row = null)
	{
		if($row != null)
		{
			parent::__construct($row);
		}
		else
		{
			parent::__construct(static::getDefaultRow());
			$this->addModifiedColumn(static::getIdField());
			$this[static::getIdField()] = null;
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
		$this->__modifiedColumns = array();
	}
	
	
	/**
	 * Find an object by ID
     * @param integer $id ID of the row in the database
     * @return object|null The record from the database
     */
	static function findId($id)
	{
		$f = static::getFactory();
		return $f->findId($id);
	}
	
	public function __clone() {
		
		$this[static::getIdField()] = null;
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
					$sql[] = $this->getTableName() . "." . $field . ' = NULL';
				}
			}
		}
		
		
		if ($this->isNewObject())
		{
			if (count($sql) > 0)
			{
				$f->update('INSERT INTO ' . $this->getTableName() . " SET " . implode(",", $sql));
			}
			else
			{
				$f->update('INSERT INTO ' . $this->getTableName() . " VALUES()");
			}

			$this[$this->getIdField()] = $f->getLastInsertId();
		}
		else if (count($sql) > 0)
		{
			$f->update('UPDATE ' . $this->getTableName() . " SET " . implode(",", $sql) . " WHERE " . $this->getTableName() . "." . $this->getIdField() . ' = ' . $this->getId());
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
		if (!$this->isNewObject())
		{
			$f = static::getFactory();
			$f->update("DELETE FROM " . $this->getTableName() . " WHERE " . $this->getIdField() . " = " . (int)$this->getId());
		}
	}

	/**
     * Get the ID (Primary Key) of the object
	 * @return integer|null ID of the record in the database. null if a new record
     */
	function getId()
	{
		return $this->getFieldValue($this->getIdField());
	}
	
	/**
     * Test if the object has been saved to the database. The primary key will be null
	 * @return boolean
     */
	function isNewObject()
	{
		return $this->getId() == null;
	}
	

	/**
     * Convert to JSON ready array. Camel case fields and the primary key is always mapped to 'id'
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
		
		if ((string)$val != "")
		{
			if (is_integer($val))
			{
				$this->setFieldValue($fieldName, date($this->getFactory()->databaseNode->getDateTimeStorageFormat(), $val));
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
	protected function setDateFieldValue($fieldName, $val)
	{
		if ($val != "" && $val != '')
		{
			if (is_integer($val))
			{
				$this->setFieldValue($fieldName, date($this->getFactory()->databaseNode->getDateTimeStorageFormat(), $val));
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
