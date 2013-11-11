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
			if ($field != $this->getIdField() && in_array($field, static::getFields()))
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
     * Clone the object as a new object. This will cause the save() to save a new row to the database.
	 * @return DataAccessObject
     */
	public function duplicateAsNewObject(){
		
		$new = clone $this;
		$new[static::getIdField()] = null;
		$new->clearModifiedColumns();
		return $new;
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
	
	protected function getFieldValue($columnName)
	{
		if (array_key_exists($columnName, $this))
		{
			return $this[$columnName];
		}
		else
		{
			throw new \Parm\Exception\GetFieldValueException($columnName . ' not initilized for get method in ' . get_class($this));
		}
	}
	
	protected function setFieldValue($columnName, $val)
	{
		if($val == NULL || strcmp($this[$columnName], $val) !== 0)
		{
			$this->addModifiedColumn($columnName);
			$this[$columnName] = $val;
		}
		
		return $this;
	}
	
	protected function setIntFieldValue($columnName, $val)
	{
		if($val == null)
		{
			return $this->setFieldValue($columnName, NULL);
		}
		else
		{
			return $this->setFieldValue($columnName,(int)$val);
		}
	}
	
	protected function getIntFieldValue($columnName)
	{
		$val = $this->getFieldValue($columnName);
		if($val == null)
		{
			return null;
		}
		else
		{
			return (int)$val;
		}
	}
	
	protected function setDateFieldValue($columnName, $mixed)
	{
		if($mixed == null)
		{
			return $this->setFieldValue($columnName, NULL);
		}
		else if($mixed instanceof \DateTime)
		{
			return $this->setFieldValue($columnName, $mixed->format($this->getFactory()->databaseNode->getDateStorageFormat()));		
		}
		else if(is_int($mixed))
		{
			$date = new \DateTime();
			$date->setTimestamp($mixed);
			return $this->setFieldValue($columnName, $date->format($this->getFactory()->databaseNode->getDateStorageFormat()));		
		}
		else
		{
			return $this->setFieldValue($columnName, $mixed);
		}
	}
	
	protected function getDateFieldValue($columnName)
	{
		return $this->getFieldValue($columnName);
	}
	
	protected function getDatetimeObjectFromField($columnName,$format)
	{
		$val = $this->getFieldValue($columnName);
		if($val != null && $format != null)
		{
			return \DateTime::createFromFormat($format, $val);
		}
		else
		{
			return null;
		}
	}
	
	protected function setDatetimeFieldValue($columnName, $mixed)
	{
		if($mixed == null)
		{
			return $this->setFieldValue($columnName, NULL);
		}
		else if($mixed instanceof \DateTime)
		{
			return $this->setFieldValue($columnName, $mixed->format($this->getFactory()->databaseNode->getDatetimeStorageFormat()));		
		}
		else if(is_int($mixed))
		{
			$date = new \DateTime();
			$date->setTimestamp($mixed);
			return $this->setFieldValue($columnName, $date->format($this->getFactory()->databaseNode->getDatetimeStorageFormat()));		
		}
		else
		{
			return $this->setFieldValue($columnName, $mixed);
		}
	}
	
	protected function getDatetimeFieldValue($columnName)
	{
		return $this->getFieldValue($columnName);
	}
	
	
	
	protected function setBooleanFieldValue($columnName, $val)
	{
		if($val == null)
		{
			return $this->setFieldValue($columnName, NULL);
		}
		else
		{
			return $this->setFieldValue($columnName,(bool)$val);
		}
	}
	
	protected function getBooleanFieldValue($columnName)
	{
		$val = $this->getFieldValue($columnName);
		if($val == null)
		{
			return null;
		}
		else
		{
			return (bool)$val;
		}
	}
	
	
	protected function setNumericalFieldValue($columnName, $val)
	{
		echo "setNumericalFieldValue";
		throw new Exception("setNumericalFieldValue");
	}
	
	protected function getNumericalFieldValue($columnName)
	{
		echo "getNumericalFieldValue";
		throw new Exception("getNumericalFieldValue");
	}
	
	protected function setLongFieldValue($columnName, $mixed)
	{
		echo "setLongFieldValue";
		throw new Exception("setLongFieldValue");
	}
	
	protected function getLongFieldValue($columnName)
	{
		echo "getLongFieldValue";
		throw new Exception("getLongFieldValue");
	}
	
//
//	/**
//     * Used by the generated classes
//     */
//	protected function setDatetimeFieldValue($fieldName, $val)
//	{
//		throw new Exception("setDatetimeFieldValue");
//		
//		if ((string)$val != "")
//		{
//			if (is_integer($val))
//			{
//				$this->setFieldValue($fieldName, date($this->getFactory()->databaseNode->getDateTimeStorageFormat(), $val));
//			}
//			else
//			{
//				
//				
//				$this->setFieldValue($fieldName, $val);
//			}
//		}
//		else
//		{
//			$this->setFieldValue($fieldName, NULL);
//		}
//	}
//	
//	/**
//     * Used by the generated classes
//     */
//	protected function setDateFieldValue($fieldName, $val)
//	{
//		throw new Exception("setDateFieldValue");
//		
//		if ($val != "" && $val != '')
//		{
//			if (is_integer($val))
//			{
//				$this->setFieldValue($fieldName, date($this->getFactory()->databaseNode->getDateTimeStorageFormat(), $val));
//			}
//			else
//			{
//				
//				
//				$this->setFieldValue($fieldName, $val);
//			}
//		}
//		else
//		{
//			$this->setFieldValue($fieldName, NULL);
//		}
//	}
//
//	/**
//     * Used by the generated classes
//     */
//	protected function getDatetimeFieldValue($fieldName, $format = null)
//	{
//		if ($format == null)
//		{
//			return $this->getFieldValue($fieldName);
//		}
//		else
//		{
//			return ($this->getFieldValue($fieldName) ? date($format, strtotime($this->getFieldValue($fieldName))) : null);
//		}
//	}
	
}
