<?php

require_once('class.DataAccessArray.php');

abstract class DataAccessObject extends DataAccessArray
{
	const NEW_OBJECT_ID = -1;
	var $modifiedColumns;

	abstract function getDatabaseName();

	abstract function getTableName();

	abstract function getIdField();

	abstract function getDefaultRow();

	function __construct($row = null)
	{
		// setup $this->data
		parent::__construct($row);

		// if $this->data is null setup with defaults from table
		if ($this->data == null)
		{
			$this->data = static::getDefaultRow();
			if (static::getIdField() != null)
			{
				$this->data[static::getIdField()] = self::NEW_OBJECT_ID;
				$this->modifiedColumns[static::getIdField()] = 1;
			}
		}
	}

	static function getFactory()
	{
		throw new SQLiciousErrorException("static getFactory must be overridden");
	}

	static function findId($id)
	{
		$f = static::getFactory();
		return $f->findId($id);
	}

	function cloneNewObject()
	{
		$obj = new static();

		// clone data
		$obj->data = $this->data;

		// set object_id to NEW_OBJECT_ID (-1)
		$obj->data[static::getIdField()] = static::NEW_OBJECT_ID;

		// set all modified colums to 1
		$obj->modifiedColumns = static::getDefaultRow();
		array_walk($obj->modifiedColumns, function(&$v, $k){ $v = 1; });

		return $obj;
	}

	function save()
	{
		$f = static::getFactory();

		if (!empty($this->modifiedColumns))
		{
			foreach (array_keys($this->modifiedColumns) as $field)
			{
				if ($field != $this->getIdField())
				{
					if ($this->data[$field] !== null)
					{
						$sql[] = $this->getTableName() . "." . $field . ' = "' . $f->escapeString($this->data[$field]) . '"';
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
					$this->data[$this->getIdField()] = $f->databaseNode->connection->insert_id;
				}
				else
				{
					throw new SQLiciousErrorException("Insert failed: " . $sql);
				}
			}

			unset($this->modifiedColumns);
		}
	}

	function delete()
	{
		if (intval($this->getId()) > 0)
		{
			$f = static::getFactory();
			$f->update("DELETE FROM " . $this->getTableName() . " WHERE " . $this->getIdField() . " = " . $this->getId());
		}
	}

	function getId()
	{
		return $this->getFieldValue($this->getIdField());
	}

	function getObjectId()
	{
		return $this->getId();
	}

	// overridden to make id always the name of the primary key
	function toJSON()
	{
		$j = array();
		if ($this->data != null)
		{
			foreach ($this->data as $field => $value)
			{
				if ($field == $this->getIdField())
				{
					$j['id'] = $value;
				}
				else
				{
					$j[static::toFieldCase($field)] = $value;
				}
			}
		}

		return $j;
	}

	function setFieldValue($fieldName, $val)
	{
		if (strcmp($this->data[$fieldName], $val) !== 0)
		{
			$this->modifiedColumns[$fieldName] = 1;
		}
		$this->data[$fieldName] = $val;
	}

	function setDatetimeFieldValue($fieldName, $val)
	{
		if ($val != "" && $val != '')
		{
			if (is_integer($val))
			{
				$this->setFieldValue($fieldName, date(SQLICIOUS_MYSQL_DATETIME_FORMAT, $val));
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

	function getDatetimeFieldValue($fieldName, $format = null)
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

?>