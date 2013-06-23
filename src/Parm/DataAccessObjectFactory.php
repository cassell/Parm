<?php

namespace Parm;

abstract class DataAccessObjectFactory extends DatabaseProcessor
{
	// generated classes create these
	abstract function getTableName();

	abstract function getIdField();

	abstract function getFields();

	abstract function getDatabaseName();
	
	private $fields = array();
	private $conditional;
	private $joinClause = '';
	private $groupByClause = '';
	private $orderByClause = '';
	private $limitClause = '';

	function __construct($databaseNode = null)
	{
		// setup connection properties
		if($databaseNode)
		{
			parent::__construct($databaseNode);
		}
		else
		{
			parent::__construct($this->getDatabaseName());
		}
		

		// fields used in the SELECT clause
		$this->setSelectFields($this->getFields());

		// conditional used in building the WHERE clause
		$this->conditional = new AndConditional();
		
		return $this;
	}
	
	/**
	 * Return and array of the objects based on Bindings
	 * @return array of DataAccessObjects
	 */
	function getObjects()
	{
		$data = array();

		$this->process(function($obj) use (&$data)
		{
			if($obj->getIdField() != null)
			{
				$data[$obj->getId()] = $obj;
			}
			else
			{
				$data[] = $obj;
			}
		});

		return $data;
	}

	/**
     * @param integer $id ID of the row in the database
     * @return object|null The record from the database
     */
	static function getObject($id)
	{
		$f = new static();

		if($f->getIdField())
		{
			$f->addBinding(new EqualsBinding($f->getIdField(), intval($id)));
			return $f->getFirstObject();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Return the first object from a getObjects
	 * @return DataAccessObject
	 */
	function getFirstObject()
	{
		$this->limit(1);
		$array = $this->getObjects();
		if($array != null && is_array($array))
		{
			return reset($array);
		}
	}

	/**
	 * Adds to the default Factory Binding which is an AND conditional
     * @param Binding|string $binding
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function addBinding($binding)
	{
		$this->conditional->addBinding($binding);
		return $this;
	}
	
	/**
	 * Adds to the default Factory Binding which is an AND conditional
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function addForeignKeyObjectBinding($object, $localField = null, $remoteField = null)
	{
		$this->addBinding(new ForeignKeyObjectBinding($object, $localField = null, $remoteField = null));
		return $this;
	}

	/**
	 * Adds a conditional to the default FactoryConditional which is an AND conditional
     * @return DataAccessObjectFactory so that you can chain bindings and conditionals
     */
	function addConditional($conditional)
	{
		$this->conditional->addConditional($conditional);	
		return $this;
	}

	function getSQL()
	{
		if($this->sql == null)
		{
			return implode(" ", array($this->getSelectClause(), $this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql(), $this->getGroupByClause(), $this->getOrderByClause(), $this->getLimitClause()));
		}
		else
		{
			return $this->sql;
		}
	}

	function delete()
	{
		$this->update("DELETE " . implode(" ", array($this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql(), $this->getGroupByClause(), $this->getOrderByClause(), $this->getLimitClause())));
	}

	// find an object or data by primary key
	function findId($id)
	{
		return $this->getObject($id);
	}

	// return all objects
	function findAll()
	{
		$this->clearBindings();
		return $this->getObjects();
	}

	// generate the select clause from $this->fields
	function getSelectClause()
	{
		return 'SELECT ' . implode(",", $this->fields);
	}

	function getFromClause()
	{
		return 'FROM ' . $this->getTableName();
	}

	function setSelectFields($arrayOfFields)
	{
		if(func_num_args() == 1 && is_array($arrayOfFields))
		{
			// empty the fields array
			$this->fields = array();

			if($this->getIdField() != null)
			{
				$this->addSelectField($this->getIdField());
			}

			foreach($arrayOfFields as $field)
			{
				if($field != $this->getIdField())
				{
					$this->addSelectField($field);
				}
			}
		}
		else
		{
			$this->setSelectFields(func_get_args());
		}
		
		return $this;
	}

	function addSelectField($field)
	{
		if(strpos($field, ".") !== false)
		{
			$this->fields[] = $field;
		}
		else
		{
			$this->fields[] = $this->getTableName() . "." . $field;
		}
		
		return $this;
	}

	// joins
	function join($clause)
	{
		$this->setJoinClause($this->getJoinClause() . " " . $clause);
		
		return $this;
	}

	function setJoinClause($val)
	{
		$this->joinClause = $val;
		
		return $this;
	}

	function getJoinClause()
	{
		return $this->joinClause;
	}

	// eventually deprecate old naming convention
	function addJoinClause($clause)
	{
		$this->join($clause);
		return $this;
	}

	// group by
	function groupBy($fieldOrArray)
	{
		if(func_num_args() > 1)
		{
			// passed multiple fields $f->addGroupBy("first_name","last_name")
			$this->groupBy(func_get_args());
		}
		else if(func_num_args() == 1 && is_array($fieldOrArray) && count($fieldOrArray) > 0)
		{
			// passed an array of fields $f->addGroupBy(["first_name","last_name"])
			foreach($fieldOrArray as $field)
			{
				$this->groupBy($field);
			}
		}
		else if(func_num_args() == 1 && is_string($fieldOrArray))
		{
			// passed a single field $f->addGroupBy("last_name")
			if($this->getGroupByClause() == "")
			{
				$this->setGroupByClause("GROUP BY " . $this->escapeString($fieldOrArray));
			}
			else
			{
				$this->setGroupByClause($this->getGroupByClause() . ", " . $this->escapeString($fieldOrArray));
			}
		}
		
		return $this;
	}

	function setGroupByClause($val)
	{
		$this->groupByClause = $val;
		
		return $this;
	}

	function getGroupByClause()
	{
		return $this->groupByClause;
	}

	// order by
	function orderBy($field, $direction = 'asc')
	{
		if($this->getOrderByClause() == "")
		{
			$this->setOrderByClause("ORDER BY ");
		}
		else
		{
			$this->setOrderByClause($this->getOrderByClause() . ", ");
		}

		$this->setOrderByClause($this->getOrderByClause() . $this->escapeString($field) . " " . $this->escapeString($direction));
		
		return $this;
	}

	function setOrderByClause($val)
	{
		$this->orderByClause = $val;
	}

	function getOrderByClause()
	{
		return $this->orderByClause;
	}

	function orderByAsc($arrayOfFields)
	{
		if(func_num_args() == 1 && is_array($arrayOfFields) && count($arrayOfFields) > 0)
		{
			foreach($arrayOfFields as $field)
			{
				$this->orderByField($field);
			}
		}
		else
		{
			$this->orderByAsc(func_get_args());
		}
		
		return $this;
	}

	// limits
	function limit($number, $offset = 0)
	{
		if((int) $offset > 0)
		{
			$this->setLimitClause("LIMIT " . (int) $offset . "," . (int) $number);
		}
		else
		{
			$this->setLimitClause("LIMIT " . (int) $number);
		}
		
		return $this;
	}

	function setLimitClause($val)
	{
		$this->limitClause = $val;
		
		return $this;
	}

	function getLimitClause()
	{
		return $this->limitClause;
	}

	function paging($pageNumber, $rowsPerPage = 20)
	{
		$this->limit($rowsPerPage, ($pageNumber - 1) * $rowsPerPage);
		
		return $this;
	}

	function count()
	{
		if($this->getIdField())
		{
			return $this->getSingleFieldFunctionValue('COUNT', $this->getTableName() . '.' . $this->getIdField());
		}
		else
		{
			return $this->getSingleFieldFunctionValue('COUNT', '*');
		}
	}

	function sum($field)
	{
		return $this->getSingleFieldFunctionValue('SUM', $field);
	}

	private function getSingleFieldFunctionValue($function, $field)
	{
		$result = $this->getMySQLResult(implode(" ", array("SELECT " . $function . "(" . $field . ") as val", $this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql())));

		if($result != null)
		{
			$row = $result->fetch_row();
			$this->freeResult($result);
			return intval($row[0]);
		}
		else
		{
			return null;
		}
	}

	// clear	
	protected function clearBindings()
	{
		$this->conditional = new AndConditional();
		
		return $this;
	}

	public function truncateTable()
	{
		$this->update("TRUNCATE TABLE " . $this->getTableName());
	}

	protected function getConditionalSql()
	{
		$conditionalSQL = $this->conditional->getSql($this);

		if($conditionalSQL != "")
		{
			$conditionalSQL = " WHERE " . $conditionalSQL;
		}

		return $conditionalSQL;
	}

	// used to do completely custom queries but bit have to write the select query
	function findObjectsWhere($whereClause)
	{
		if(count($this->conditional->items) > 0)
		{
			throw new SQLiciousErrorException("Bindings have been added to the factory but are not respected by the findObjectsWhere method. Use getObjects, getArray, etc.");
		}

		$this->setSQL($this->getSelectClause() . " " . $this->getFromClause() . " " . $whereClause);

		return $this->getObjects();
	}

// deprecate below

	function find($clause = "")
	{
		return $this->findObjectsWhere($clause);
	}

	function deleteWhere($whereClause)
	{
		return $this->update("DELETE FROM " . $this->getTableName() . " WHERE " . $whereClause);
	}

	// find the first object matching the clause
	function findFirst($clause = "")
	{
		$a = $this->find($clause . " LIMIT 1");
		if($a != null)
		{
			return reset($a);
		}
		else
		{
			return null;
		}
	}

	function findDistinctField($field, $clause = "")
	{
		$array = array();

		$result = $this->getMySQLResult('SELECT DISTINCT(' . $this->escapeString($field) . ") as fdf FROM " . $this->getTableName() . " " . $clause);

		if($result != null && $result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$array[] = $row["fdf"];
			}

			$this->freeResult($result);
		}

		return $array;
	}

	function findField($field, $clause = "")
	{
		$array = array();

		$result = $this->getMySQLResult('SELECT ' . $this->escapeString($field) . " as ff FROM " . $this->getTableName() . " " . $clause);

		if($result != null && $result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$array[] = $row["ff"];
			}

			$this->freeResult($result);
		}

		return $array;
	}

	function findFirstField($field, $clause = "")
	{
		$a = $this->findField($field, $clause . " LIMIT 1");
		
		if($a)
		{
			return reset($a);
		}
		else
		{
			return null;
		}
	}

	function getCount($clause = "")
	{
		if($this->getIdField() != '')
		{
			return (int)($this->sqlFunctionFieldQuery('COUNT', $this->getTableName() . "." . $this->getIdField(), $clause));
		}
		else
		{
			return (int)($this->sqlFunctionFieldQuery('COUNT', '*', $clause));
		}
	}

	function getMaxField($field, $clause = "")
	{
		return $this->sqlFunctionFieldQuery('MAX', $field, $clause);
	}

	function getSumField($field, $clause = "")
	{
		return $this->sqlFunctionFieldQuery('SUM', $field, $clause);
	}

	private function sqlFunctionFieldQuery($sqlFunction, $field, $clause)
	{
		return reset($this->findField($sqlFunction . '(' . $this->escapeString($field) . ')', $clause));
	}

	// deprecate old naming convetion
	function orderByField($field, $direction = 'asc')
	{
		$this->orderBy($field, $direction);
	}

	// depecate
	function executeQuery($sql)
	{
		$data = array();

		$result = $this->getMySQLResult($sql);

		if($result != null && $result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$data[] = $row;
			}

			$this->freeResult($result);
		}

		return $data;
	}

}

?>