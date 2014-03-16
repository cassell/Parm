<?php

namespace Parm;

abstract class DataAccessObjectFactory extends DatabaseProcessor implements TableInterface
{
	private $fields = array();
	private $conditional;
	private $joinClause = '';
	private $groupByClause = '';
	private $orderByClause = '';
	private $limitClause = '';

	/**
	 * @param DatabaseNode $databaseNode optional The database to retrieve the objects from
     */
	function __construct(DatabaseNode $databaseNode = null)
	{
		// setup connection properties
		if($databaseNode)
		{
			parent::__construct($databaseNode);
		}
		else
		{
			parent::__construct(static::getDatabaseName());
		}
		
		// fields used in the SELECT clause
		$this->setSelectFields(static::getFields());

		// conditional used in building the WHERE clause
		$this->conditional = new Binding\Conditional\AndConditional();
	}
	
	/**
	 * Return an array of the objects based on Bindings
	 * @return array DataAccessObjects
	 */
	function getObjects()
	{
		$data = array();

		$this->process(function($obj) use (&$data)
		{
			if($obj::getIdField() != null)
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
	 * Return the first object from a getObjects
	 * @return object
	 */
	function getFirstObject()
	{
		$this->limit(1);
		$array = $this->getObjects();
		if($array != null && is_array($array))
		{
			return reset($array);
		}
		else return null;
	}
	

	/**
	 * Find an object by primary key
     * @param integer $id ID of the row in the database
     * @return object|null The record from the database
     */
	function findId($id)
	{
		$this->clearBindings();

		if(static::getIdField())
		{
			$this->addBinding(new Binding\EqualsBinding(static::getIdField(), (int)$id));
			return $this->getFirstObject();
		}
		else
		{
			return null;
		}
	}
	
	/**
     * @param integer $id ID of the row in the database
     * @return object|null The record from the database
	 * @throws \Parm\Exception\RecordNotFoundException
     */
	function findIdOrFail($id)
	{
		$object = $this->findId($id);
		
		if($object)
		{
			return $object;
		}
		else
		{
			throw new \Parm\Exception\RecordNotFoundException('Unable to find object #' . (int)$id);
		}
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
	 * Get objects with completely custom join, where, group, and order by clause
	 * @param string $whereClause
	 * @return array of DataAccessObjects
     */
	function find($clause = "")
	{
		if($this->conditional->hasChildBindings())
		{
			throw new \Parm\Exception\ErrorException("Bindings have been added to the factory but are not respected by the find method. Use getObjects, getArray, etc. For the find() method you must pass the entire join, where, group, and order by clauses as a string");
		}
		
		$this->setSQL($this->getSelectClause() . " " . $this->getFromClause() . " " . $clause);

		return $this->getObjects();
	}

	/**
	 * Adds to the default factory Binding which is an AND conditional
     * @param Binding|string $binding
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function addBinding($binding)
	{
		$this->conditional->addBinding($binding);
		return $this;
	}
	
	/**
	 * Alias to addBinding. Adds a binding to the default factory Binding which is an AND conditional
     * @param Binding|string $binding
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function bind($binding)
	{
		$this->conditional->addBinding($binding);
		return $this->addBinding($binding);
	}
	
	/**
	 * Adds a conditional to the default FactoryConditional which is an AND conditional
	 * @param Parm\Binding\Conditional $conditional
     * @return DataAccessObjectFactory so that you can chain bindings and conditionals
     */
	function addConditional(Binding\Conditional\Conditional $conditional)
	{
		$this->conditional->addConditional($conditional);	
		return $this;
	}
	
	/**
	 * Adds to the default Factory Binding which is an AND conditional
	 * @param DataAccessObject $object
	 * @param string $localField
	 * @param string $remoteField 
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function addForeignKeyObjectBinding(DataAccessObject $object, $localField = null, $remoteField = null)
	{
		return $this->addBinding(new Binding\ForeignKeyObjectBinding($object, $localField = null, $remoteField = null));
	}
	
	/**
	 * Shorthand to add an Equals Binding to the Factory conditional
	 * @param string $field
	 * @param string $value 
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function whereEquals($field,$value)
	{
		return $this->addBinding(new Binding\EqualsBinding($field, $value));
	}
	
	/**
	 * Shorthand to add a NotEqualsBinding to the Factory conditional
	 * @param string $field
	 * @param string $value 
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function whereNotEquals($field,$value)
	{
		return $this->addBinding(new Binding\NotEqualsBinding($field, $value));
	}
	
	/**
	 * Shorthand to add a ContainsBinding to the Factory conditional
	 * @param string $field
	 * @param string $value 
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function whereContains($field,$value)
	{
		return $this->addBinding(new Binding\ContainsBinding($field, $query));
	}
	
	/**
	 * Shorthand to add an TrueBooleanBinding Binding to the Factory conditional
	 * @param string $field
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function whereTrue($field)
	{
		return $this->addBinding(new Binding\TrueBooleanBinding($field));
	}
	
	/**
	 * Shorthand to add an TrueBooleanBinding Binding to the Factory conditional
	 * @param string $field
     * @return DataAccessObjectFactory so that you can chain bindings
     */
	function whereFalse($field)
	{
		return $this->addBinding(new Binding\FalseBooleanBinding($field));
	}
	

	/**
	 * Get the SQL that will be executed against the database. This is useful for debugging.
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
		if(strpos(static::getTableName(), "-") !== false)
		{
			return 'FROM `' . static::getTableName() . '`';
		}
		else
		{
			return 'FROM ' . static::getTableName();
		}
		
	}

	/**
	 * Set the coluns to return from the database. You would use this to limit the number of fields return per row
	 * or select other fields from a join clause.
	 * Usage: $f->setSelectFields("first_name","last_name","email");
	 * @param array|string|strings $arrayOfFields An array of strings or all the fields separated by commas
	 * @return DataAccessObject
     */
	function setSelectFields($arrayOfFields)
	{
		if($arrayOfFields == null)
		{
			throw new Exception\ErrorException('setSelectFields($arrayOfFields) $arrayOfFields can\'t be null.');
		}
		else if(func_num_args() == 1 && is_array($arrayOfFields))
		{
			if(count($arrayOfFields) > 0)
			{
				// empty the fields array
				$this->fields = array();

				if(static::getIdField() != null)
				{
					$this->addSelectField(static::getIdField());
				}

				foreach($arrayOfFields as $field)
				{
					if($field != static::getIdField())
					{
						$this->addSelectField($field);
					}
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
	 * @param string $field The name of the column
	 * @return DataAccessObject for chaining
     */
	function addSelectField($field)
	{
		if(strpos($field, "-") !== false)
		{
			$field =  "`".$field."`";
		}
		
		if(strpos($field, ".") !== false)
		{
			$this->fields[] = $field;
		}
		else
		{
			if(strpos(static::getTableName(), "-") !== false)
			{
				$this->fields[] = "`".static::getTableName() . "`." . $field;
			}
			else
			{
				$this->fields[] = static::getTableName() . "." . $field;
			}
		}
		
		return $this;
	}

	/**
	 * Add a join to the select clause
	 * @param string $clause The join clause
	 * @return DataAccessObject for chaining
     */
	function join($clause)
	{
		$this->setJoinClause($this->getJoinClause() . " " . $clause);
		
		return $this;
	}

	/**
	 * Set and replace the entire join clause
	 * @param string $val The join clause
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
	 * @param string $clause join clause
	 * @return DataAccessObject
     */
	function addJoinClause($clause)
	{
		return $this->join($clause);
	}

	/**
	 * Add to the group by clause
	 * Usage: $f->groupBy("last_name");
	 * @param array|string $fieldOrArray An array of strings or all the fields separated by commas
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
	 * Set and replace the entire group yb clause
	 * @param string $groupByClause The group by clause
	 * @return DataAccessObject for chaining
     */
	function setGroupByClause($groupByClause)
	{
		$this->groupByClause = $groupByClause;
		
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
	 * Set and eplace the entire order by clause
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
	 * @param array|string $arrayOfFields An array of strings or all the fields separated by commas
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

	/**
	 * Limit the number of rows returned by the database
	 * Usage: $f->limit(10);
	 * @param integer $number The number of rows to return
	 * @param integer $offset The row to start at
	 * @return DataAccessObject
     */
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
	
	/**
	 * Set and replace the entire limit clause
	 * @param string $limitClause The limit clause
	 * @return DataAccessObject for chaining
     */
	function setLimitClause($limitClause)
	{
		$this->limitClause = $limitClause;
		
		return $this;
	}

	/**
	 * Get the limit clause
	 * @return string
     */
	function getLimitClause()
	{
		return $this->limitClause;
	}

	/**
	 * Wrapper to the limit function to paging to page through data
	 * Usage: $f->paging(2,50);
	 * @param integer $pageNumber The page number the dataset is on
	 * @param integer $rowsPerPage Number of rows per page
	 * @return DataAccessObject
     */
	function paging($pageNumber, $rowsPerPage = 20)
	{
		$this->limit($rowsPerPage, ($pageNumber - 1) * $rowsPerPage);
		
		return $this;
	}

	/**
	 * Get count of the number of rows from the database (based on Bindings).
	 * @return integer The count of rows
     */
	function count()
	{
		if(static::getIdField())
		{
			return $this->getSingleFieldFunctionValue('COUNT', static::getTableName() . '.' . static::getIdField());
		}
		else
		{
			return $this->getSingleFieldFunctionValue('COUNT', '*');
		}
	}

	/**
	 * Sum a field in the rows returned (based on Bindings).
	 * @return integer
     */
	function sum($field)
	{
		return $this->getSingleFieldFunctionValue('SUM', $field);
	}

	/**
	 * Truncate all the data in the table
     */
	function truncateTable()
	{
		$this->update("TRUNCATE TABLE " . static::getTableName());
	}


	/**
	 * Delete rows based on custom where clause
	 * @param string $whereClause
     */
	function deleteWhere($whereClause)
	{
		return $this->update("DELETE FROM " . static::getTableName() . " WHERE " . $whereClause);
	}

	/**
	 * Find the first object based on the clause
	 * @param string $clause
	 * @return DataAccessObject
     */
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

	/**
	 * Find distinct values of a single column. Can be filtered by the clause
	 * @param string $field The field to select
	 * @param string $clause The the clause to filter on
	 * @return array
     */
	function findDistinctField($field, $clause = "")
	{
		$array = array();

		$result = $this->getMySQLResult('SELECT DISTINCT(' . $this->escapeString($field) . ") as fdf FROM " . static::getTableName() . " " . $clause);

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

	/**
	 * Find all the values of a single column. Can be filtered by the clause
	 * @param string $field The field to select
	 * @param string $clause The the clause to filter on
	 * @return array
     */
	function findField($field, $clause = "")
	{
		$array = array();

		$result = $this->getMySQLResult('SELECT ' . $this->escapeString($field) . " as ff FROM " . static::getTableName() . " " . $clause);

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

	/**
	 * Find the first value of a single column. Can be filtered by the clause
	 * @param string $field The field to select
	 * @param string $clause The the clause to filter on
	 * @return string
     */
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

	/**
	 * Get a count of rows based on the filter clause
	 * @param string $clause The the clause to filter on
	 * @return integer the count of rows
     */
	function getCount($clause = "")
	{
		if($this->getIdField() != '')
		{
			return (int)($this->sqlFunctionFieldQuery('COUNT', static::getTableName() . "." . static::getIdField(), $clause));
		}
		else
		{
			return (int)($this->sqlFunctionFieldQuery('COUNT', '*', $clause));
		}
	}

	/**
	 * Find the maximum value of a single column. Can be filtered by the clause
	 * @param string $field The field to get the max value of
	 * @param string $clause The the clause to filter on
	 * @return string
     */
	function getMaxField($field, $clause = "")
	{
		return $this->sqlFunctionFieldQuery('MAX', $field, $clause);
	}

	/**
	 * Find the sum of values for a single column. Can be filtered by the clause
	 * @param string $field The field to sum
	 * @param string $clause The the clause to filter on
	 * @return string
     */
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
	
	protected function getConditionalSql()
	{
		$conditionalSQL = $this->conditional->getSql($this);

		if($conditionalSQL != "")
		{
			$conditionalSQL = " WHERE " . $conditionalSQL;
		}

		return $conditionalSQL;
	}

	protected function clearBindings()
	{
		$this->conditional = new Binding\Conditional\AndConditional();
		
		return $this;
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

}
