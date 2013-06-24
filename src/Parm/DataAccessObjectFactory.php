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
		$this->conditional = new Binding\Conditional\AndConditional();
		
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
	 * @param DataAccessObject $object
	 * @param string $localField
	 * @param string $remoteField 
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

	/**
	 * Get the SQL  that will be executed against the database
     * @return string
     */
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

	/**
	 * Delete based bindings
     */
	function delete()
	{
		$this->update("DELETE " . implode(" ", array($this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql(), $this->getGroupByClause(), $this->getOrderByClause(), $this->getLimitClause())));
	}

	/**
	 * Find an object by primary key
	 * @return object
     */
	function findId($id)
	{
		return $this->getObject($id);
	}

	/**
	 * Return all the rows in a table
	 * @return array of objects
     */
	function findAll()
	{
		$this->clearBindings();
		return $this->getObjects();
	}

	/**
	 * Generate the SELECT clause from $this->fields
	 * @return string
     */
	function getSelectClause()
	{
		return 'SELECT ' . implode(",", $this->fields);
	}

	/**
	 * Generate the FROM clause from the table name
	 * @return string
     */
	function getFromClause()
	{
		return 'FROM ' . $this->getTableName();
	}

	/**
	 * Set the coluns to return from the database. You would use this to limit the number of fields return per row
	 * or select other fields from a join clause.
	 * Usage: $f->setSelectFields("first_name","last_name","email");
	 * @param array|string An array of strings or all the fields separated by commas
	 * @return DataAccessObject
     */
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

	/**
	 * Add a column to the select clause. Useful when using join.
	 * Usage: $f->addSelectField("company.company_name");
	 * @param string The name of the column
	 * @return DataAccessObject for chaining
     */
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

	/**
	 * Add a join to the select clause
	 * @param string The join clause
	 * @return DataAccessObject for chaining
     */
	function join($clause)
	{
		$this->setJoinClause($this->getJoinClause() . " " . $clause);
		
		return $this;
	}

	/**
	 * Set the join clause, this replaces the entire join clause
	 * @param string The join clause
	 * @return DataAccessObject for chaining
     */
	function setJoinClause($val)
	{
		$this->joinClause = $val;
		
		return $this;
	}

	/**
	 * Get the join clause
	 * @return string
     */
	function getJoinClause()
	{
		return $this->joinClause;
	}

	/**
	 * Alias to the join() function
	 * @param string join clause
	 * @return DataAccessObject
     */
	function addJoinClause($clause)
	{
		return $this->join($clause);
	}

	/**
	 * Add to the group by clause
	 * Usage: $f->groupBy("last_name");
	 * @param array|string An array of strings or all the fields separated by commas
	 * @return DataAccessObject
     */
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

	/**
	 * Set the group by clause, this replaces the entire group by clause
	 * @param string The group by clause
	 * @return DataAccessObject for chaining
     */
	function setGroupByClause($val)
	{
		$this->groupByClause = $val;
		
		return $this;
	}

	/**
	 * Get the group by clause
	 * @return string
     */
	function getGroupByClause()
	{
		return $this->groupByClause;
	}

	/**
	 * Add to the order by clause
	 * Usage: $f->orderBy("last_name","asc");
	 * @param string $field The field to sort by
	 * @param string $direction The direction to sort. asc or desc
	 * @return DataAccessObject
     */
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

	/**
	 * Set the order by clause, this replaces the entire order by clause
	 * @param string The order by clause
	 * @return DataAccessObject for chaining
     */
	function setOrderByClause($val)
	{
		$this->orderByClause = $val;
		return $this;
	}

	/**
	 * Get the order by clause
	 * @return string
     */
	function getOrderByClause()
	{
		return $this->orderByClause;
	}

	/**
	 * Add a bunch of fields to the order by clause ascending
	 * Usage: $f->orderByAsc("last_name","first_name");
	 * @param array|string An array of strings or all the fields separated by commas
	 * @return DataAccessObject
     */
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