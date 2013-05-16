<?php

require_once('class.DatabaseProcessor.php');

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

	function __construct()
	{
		// setup connection properties
		parent::__construct($this->getDatabaseName());

		// fields used in the SELECT clause
		$this->setSelectFields($this->getFields());

		// conditional used in building the WHERE clause
		$this->conditional = new FactoryConditional();
	}
	
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

	function getFirstObject()
	{
		$this->limit(1);
		$array = $this->getObjects();
		if($array != null && is_array($array))
		{
			return reset($array);
		}
	}

	function addBinding($binding)
	{
		$this->conditional->addBinding($binding);
	}
	
	function addForeignKeyObjectBinding($object, $localField = null, $remoteField = null)
	{
		$this->addBinding(new ForeignKeyObjectBinding($object, $localField = null, $remoteField = null));
	}

	function addConditional($conditional)
	{
		$this->conditional->addConditional($conditional);
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
	}

	// joins
	function join($clause)
	{
		$this->setJoinClause($this->getJoinClause() . " " . $clause);
	}

	function setJoinClause($val)
	{
		$this->joinClause = $val;
	}

	function getJoinClause()
	{
		return $this->joinClause;
	}

	// eventually deprecate old naming convention
	function addJoinClause($clause)
	{
		$this->join($clause);
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
	}

	function setGroupByClause($val)
	{
		$this->groupByClause = $val;
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
	}

	function setLimitClause($val)
	{
		$this->limitClause = $val;
	}

	function getLimitClause()
	{
		return $this->limitClause;
	}

	function paging($pageNumber, $rowsPerPage = 20)
	{
		$this->limit($rowsPerPage, ($pageNumber - 1) * $rowsPerPage);
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
		$this->conditional = new FactoryConditional();
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

	/* below are functions that are slowly being phased out */

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

/* abstract sql string class, bindings and conditionals extend this class */

abstract class SQLString
{

	function __construct()
	{
		
	}

	abstract protected function getSQL($factory);
}

/* used to logical AND together bindings */

class Conditional extends SQLString
{

	var $items = array();

	function __construct()
	{
		parent::__construct();
	}

	function addBinding($binding)
	{
		if(is_string($binding))
		{
			$this->addItem(new StringBinding($binding));
		}
		else
		{
			$this->addItem($binding);
		}
	}

	function addConditional($conditional)
	{
		$this->addItem($conditional);
	}

	private function addItem($item)
	{
		$this->items[] = $item;
	}

	function getSQL($factory)
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();

			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL($factory);
			}

			return "(" . implode(" AND ", $sql) . ")";
		}
		else
			return '';
	}

}

/* used to logical OR together bidnings */

class OrConditional extends Conditional
{

	function __construct()
	{
		parent::__construct();
	}

	function getSQL($factory)
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();

			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL($factory);
			}

			return "(" . implode(" OR ", $sql) . ")";
		}
		else
			return '';
	}

}

// FactoryConditional is an AND conditional with WHERE in front an no parenthesis, used in DaoAccessObjectFactory
class FactoryConditional extends Conditional
{

	function __construct()
	{
		parent::__construct();
	}

	function getSQL($factory)
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();

			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL($factory);
			}

			return implode(" AND ", $sql);
		}
		else
			return '';
	}

}

/* logical test binding */

class Binding extends SQLString
{

	function __construct($field, $operator, $value)
	{
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
		parent::__construct();
	}

	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString($this->value) . "'";
	}

}

/* string binding is a simple string */

class StringBinding extends SQLString
{

	function __construct($sql)
	{
		$this->sql = $sql;
		parent::__construct();
	}

	function getSQL($factory)
	{
		return $this->sql;
	}

}

/* test if equal */

class EqualsBinding extends Binding
{

	function __construct($field, $value)
	{
		parent::__construct($field, '=', $value);
	}

}

class CaseSensitiveEqualsBinding extends EqualsBinding
{
	function __construct($field, $value)
	{
		parent::__construct($field, $value);
	}

	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " COLLATE " . $factory->databaseNode->serverCaseSensitiveCollation . " LIKE '" . $factory->escapeString(str_replace("_", "\_", str_replace("%", "\%", $this->value))) . "'";
	}
}

class NotEqualsBinding extends Binding
{

	function __construct($field, $value)
	{
		parent::__construct($field, '!=', $value);
	}

}

class TrueBooleanBinding extends Binding
{

	function __construct($field)
	{
		parent::__construct($field, '=', '1');
	}

}

class FalseBooleanBinding extends Binding
{

	function __construct($field)
	{
		parent::__construct($field, '=', '0');
	}

}

/* see if field contains query */

class ContainsBinding extends SQLString
{

	function __construct($field, $query)
	{
		parent::__construct();

		$this->field = $field;
		$this->query = $query;
	}

	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " LIKE '%" . $factory->escapeString(str_replace("_", "\_", str_replace("%", "\%", $this->query))) . "%'";
	}
}



class InBinding extends SQLString
{

	function __construct($field, $array)
	{
		parent::__construct();
		$this->field = $field;
		$this->array = $array;
	}

	function getSQL($factory)
	{
		if(count($this->array) == 1)
		{
			return $factory->escapeString($this->field) . " = " . reset($this->array);
		}
		else if(count($this->array) > 0)
		{
			foreach($this->array as $key => $item)
			{
				if(is_numeric($item))
				{
					$this->array[$key] = (int) $item;
				}
				else
				{
					$this->array[$key] = "'" . $factory->escapeString($item) . "'";
				}
			}

			return $factory->escapeString($this->field) . " IN (" . implode(",", $this->array) . ")";
		}
		else
		{
			throw new SQLiciousErrorException("The array passed to the InBinding is empty");
		}
	}

}

class NotInBinding extends SQLString
{

	function __construct($field, $array)
	{
		parent::__construct();
		$this->field = $field;
		$this->array = $array;
	}

	function getSQL($factory)
	{
		if(count($this->array > 0))
		{
			foreach($this->array as $key => $item)
			{
				$this->array[$key] = $factory->escapeString($item);
			}

			return $factory->escapeString($this->field) . " NOT IN (" . implode(",", $this->array) . ")";
		}
		else
		{
			throw new SQLiciousErrorException("The array passed to the NotInBinding is empty");
		}
	}

}

class ForeignKeyObjectBinding extends EqualsBinding
{
	function __construct($object, $localField = null, $remoteField = null)
	{
		if($localField == null)
		{
			$localField = $object->getIdField();
		}
		
		if($remoteField == null)
		{
			$value = $object->getId();
		}
		else
		{
			$value = $object->getFieldValue($remoteField);
		}
		
		parent::__construct($localField, $value);
	}
}

?>