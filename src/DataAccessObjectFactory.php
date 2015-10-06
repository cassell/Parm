<?php

namespace Parm;

use Parm\Binding\Binding;
use Parm\Binding\Conditional\AndConditional;
use Parm\Binding\Conditional\Conditional;
use Parm\Binding\ContainsBinding;
use Parm\Binding\EqualsBinding;
use Parm\Binding\FalseBooleanBinding;
use Parm\Binding\ForeignKeyObjectBinding;
use Parm\Binding\NotEqualsBinding;
use Parm\Binding\TrueBooleanBinding;
use Parm\Exception\ErrorException;
use Parm\Exception\RecordNotFoundException;

abstract class DataAccessObjectFactory extends DatabaseProcessor implements TableInterface
{
    private $fields = array();
    private $conditional;
    private $joinClause = '';
    private $groupByClause = '';
    private $orderByClause = '';
    private $limitClause = '';

    /**
     * @param \Doctrine\DBAL\Connection|string|null $connection The database to connect to
     */
    public function __construct($connection = null)
    {
        // setup connection properties
        if ($connection) {
            parent::__construct($connection);
        } else {
            parent::__construct(static::getDatabaseName());
        }

        // fields used in the SELECT clause
        $this->setSelectFields(static::getFields());

        // conditional used in building the WHERE clause
        $this->conditional = new AndConditional();
    }

    /**
     * Return an array of the objects based on Bindings
     * @return array DataAccessObjects
     */
    public function getObjects()
    {
        $data = array();

        if (static::getIdField() != null) {
            foreach ($this->getCollection() as $object) {
                $data[$object->getId()] = $object;
            }
        } else {
            foreach ($this->getCollection() as $object) {
                $data[] = $object;
            }
        }

        $this->clearBindings();

        return $data;

    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return new Collection($this);
    }

    /**
     * Return the first object from a getObjects
     * @return object
     */
    public function getFirstObject()
    {
        $this->limit(1);
        $array = $this->getObjects();
        if ($array != null && is_array($array)) {
            return reset($array);
        } else return null;
    }

    /**
     * Find an object by primary key
     * @param  integer|string $id ID of the row in the database
     * @return null|object    The record from the database
     * @throws ErrorException
     */
    public function findId($id)
    {
        $this->clearBindings();

        if (static::getIdField()) {
            $this->addBinding(new EqualsBinding(static::getIdField(), $id));

            return $this->getFirstObject();
        } else {
            throw new ErrorException('Unable to findId on a table that does not have a primary key');
        }
    }

    /**
     * @param  integer|string          $id ID of the row in the database
     * @return object|null             The record from the database
     * @throws RecordNotFoundException
     * @throws ErrorException
     */
    public function findIdOrFail($id)
    {
        $object = $this->findId($id);

        if ($object) {
            return $object;
        } else {
            throw new RecordNotFoundException('Unable to find object #' . $id);
        }
    }

    /**
     * Return all the rows in a table
     * @return array of objects
     */
    public function findAll()
    {
        $this->clearBindings();

        return $this->getObjects();
    }

    /**
     * Get objects with completely custom join, where, group, and order by clause
     * @param  string         $clause
     * @return array          of DataAccessObjects
     * @throws ErrorException
     */
    public function find($clause = "")
    {
        if ($this->conditional->hasChildBindings()) {
            throw new ErrorException("Bindings have been added to the factory but are not respected by the find method. Use getObjects, getArray, etc. For the find() method you must pass the entire join, where, group, and order by clauses as a string");
        }

        $this->setSQL($this->getSelectClause() . " " . $this->getFromClause() . " " . $clause);

        return $this->getObjects();
    }

    /**
     * Adds to the default factory Binding which is an AND conditional
     * @param  Binding|string          $binding
     * @return DataAccessObjectFactory so that you can chain bindings
     */
    public function addBinding($binding)
    {
        $this->conditional->addBinding($binding);

        return $this;
    }

    /**
     * Alias to addBinding. Adds a binding to the default factory Binding which is an AND conditional
     * @param  Binding|string          $binding
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function bind($binding)
    {
        return $this->addBinding($binding);
    }

    /**
     * Adds a conditional to the default FactoryConditional which is an AND conditional
     * @param  Conditional             $conditional
     * @return DataAccessObjectFactory so that you can chain bindings and conditionals
     * @codeCoverageIgnore
     */
    public function addConditional(Conditional $conditional)
    {
        $this->conditional->addConditional($conditional);

        return $this;
    }

    /**
     * Adds to the default Factory Binding which is an AND conditional
     * @param  DataAccessObject        $object
     * @param  string                  $localField
     * @param  string                  $remoteField
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function addForeignKeyObjectBinding(DataAccessObject $object, $localField = null, $remoteField = null)
    {
        return $this->addBinding(new ForeignKeyObjectBinding($object, $localField = null, $remoteField = null));
    }

    /**
     * Shorthand to add an Equals Binding to the Factory conditional
     * @param  string                  $field
     * @param  string                  $value
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function whereEquals($field, $value)
    {
        return $this->addBinding(new EqualsBinding($field, $value));
    }

    /**
     * Shorthand to add a NotEqualsBinding to the Factory conditional
     * @param  string                  $field
     * @param  string                  $value
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function whereNotEquals($field, $value)
    {
        return $this->addBinding(new NotEqualsBinding($field, $value));
    }

    /**
     * Shorthand to add a ContainsBinding to the Factory conditional
     * @param  string                  $field
     * @param  string                  $value
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function whereContains($field, $value)
    {
        return $this->addBinding(new ContainsBinding($field, $value));
    }

    /**
     * Shorthand to add an TrueBooleanBinding Binding to the Factory conditional
     * @param  string                  $field
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function whereTrue($field)
    {
        return $this->addBinding(new TrueBooleanBinding($field));
    }

    /**
     * Shorthand to add an TrueBooleanBinding Binding to the Factory conditional
     * @param  string                  $field
     * @return DataAccessObjectFactory so that you can chain bindings
     * @codeCoverageIgnore
     */
    public function whereFalse($field)
    {
        return $this->addBinding(new FalseBooleanBinding($field));
    }

    /**
     * Get the SQL that will be executed against the database. This is useful for debugging.
     * @return string
     */
    public function getSQL()
    {
        if ($this->sql == null) {
            return implode(" ", array($this->getSelectClause(), $this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql(), $this->getGroupByClause(), $this->getOrderByClause(), $this->getLimitClause()));
        } else {
            return $this->sql;
        }
    }

    /**
     * Delete based bindings
     */
    public function delete()
    {
        $this->update("DELETE " . implode(" ", array($this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql(), $this->getGroupByClause(), $this->getOrderByClause(), $this->getLimitClause())));
    }

    /**
     * Generate the SELECT clause from $this->fields
     * @return string
     */
    public function getSelectClause()
    {
        return 'SELECT ' . implode(",", $this->fields);
    }

    /**
     * Generate the FROM clause from the table name
     * @return string
     */
    public function getFromClause()
    {
        return 'FROM ' . static::getEscapedTableName();
    }

    /**
     * Set the columns to return from the database. You would use this to limit the number of fields return per row
     * or select other fields from a join clause.
     * Usage: $f->setSelectFields("first_name","last_name","email");
     * @param  array|string|string[] $arrayOfFields An array of strings or all the fields separated by commas
     * @return DataAccessObject
     * @throws ErrorException
     */
    public function setSelectFields($arrayOfFields)
    {
        if ($arrayOfFields == null) {
            throw new Exception\ErrorException('setSelectFields($arrayOfFields) $arrayOfFields can\'t be null.');
        } elseif (func_num_args() == 1 && is_array($arrayOfFields)) {
            if (count($arrayOfFields) > 0) {
                // empty the fields array
                $this->fields = array();

                if (static::getIdField() != null) {
                    $this->addSelectField(static::getIdField());
                }

                foreach ($arrayOfFields as $field) {
                    if ($field != static::getIdField()) {
                        $this->addSelectField($field);
                    }
                }
            }
        } else {
            $this->setSelectFields(func_get_args());
        }

        return $this;
    }

    /**
     * Add a column to the select clause. Useful when using join.
     * Usage: $f->addSelectField("company.company_name");
     * @param  string                  $field The name of the column
     * @return DataAccessObjectFactory for chaining
     */
    public function addSelectField($field)
    {
        if (strpos($field, "-") !== false) {
            $field = "`" . $field . "`";
        }

        if (strpos($field, ".") !== false) {
            $this->fields[] = $field;
        } else {
            $this->fields[] = static::getEscapedTableName() . "." . $field;
        }

        return $this;
    }

    /**
     * Add a join to the select clause
     * @param  string                  $clause The join clause
     * @return DataAccessObjectFactory for chaining
     */
    public function join($clause)
    {
        $this->setJoinClause($this->getJoinClause() . " " . $clause);

        return $this;
    }

    /**
     * Set and replace the entire join clause
     * @param  string                  $val The join clause
     * @return DataAccessObjectFactory for chaining
     */
    public function setJoinClause($val)
    {
        $this->joinClause = $val;

        return $this;
    }

    /**
     * Get the join clause
     * @return string
     */
    public function getJoinClause()
    {
        return $this->joinClause;
    }

    /**
     * Alias to the join() function
     * @param  string           $clause join clause
     * @return DataAccessObject
     * @codeCoverageIgnore
     */
    public function addJoinClause($clause)
    {
        return $this->join($clause);
    }

    /**
     * Add to the group by clause
     * Usage: $f->groupBy("last_name");
     * @param  array|string     $fieldOrArray An array of strings or all the fields separated by commas
     * @return DataAccessObject
     */
    public function groupBy($fieldOrArray)
    {
        if (func_num_args() > 1) {
            // passed multiple fields $f->addGroupBy("first_name","last_name")
            $this->groupBy(func_get_args());
        } elseif (func_num_args() == 1 && is_array($fieldOrArray) && count($fieldOrArray) > 0) {
            // passed an array of fields $f->addGroupBy(["first_name","last_name"])
            foreach ($fieldOrArray as $field) {
                $this->groupBy($field);
            }
        } elseif (func_num_args() == 1 && is_string($fieldOrArray)) {
            // passed a single field $f->addGroupBy("last_name")
            if ($this->getGroupByClause() == "") {
                $this->setGroupByClause("GROUP BY " . $fieldOrArray);
            } else {
                $this->setGroupByClause($this->getGroupByClause() . ", " . $fieldOrArray);
            }
        }

        return $this;
    }

    /**
     * Set and replace the entire group yb clause
     * @param  string                  $groupByClause The group by clause
     * @return DataAccessObjectFactory for chaining
     */
    public function setGroupByClause($groupByClause)
    {
        $this->groupByClause = $groupByClause;

        return $this;
    }

    /**
     * Get the group by clause
     * @return string
     */
    public function getGroupByClause()
    {
        return $this->groupByClause;
    }

    /**
     * Usage: $f->orderBy("last_name","asc");
     * Alt Usage: $f->orderBy("last_name, first_name");
     * @param $fieldOrSort string field to sort on or the order by clause
     * @param  string $direction
     * @return $this
     */
    public function orderBy($fieldOrSort, $direction = 'asc')
    {
        if ($this->getOrderByClause() == "") {
            $this->setOrderByClause("ORDER BY ");
        } else {
            $this->setOrderByClause($this->getOrderByClause() . ", ");
        }

        if (!strpos($fieldOrSort, " ") && !strpos($fieldOrSort, ",")) {
            $this->setOrderByClause($this->getOrderByClause() . $fieldOrSort . " " . $direction);
        } else {
            $this->setOrderByClause($this->getOrderByClause() . $fieldOrSort);
        }

        return $this;
    }

    /**
     * Set and replace the entire order by clause
     * @param  string                  $val order by clause
     * @return DataAccessObjectFactory for chaining
     */
    public function setOrderByClause($val)
    {
        $this->orderByClause = $val;

        return $this;
    }

    /**
     * Get the order by clause
     * @return string
     */
    public function getOrderByClause()
    {
        return $this->orderByClause;
    }

    /**
     * Add a bunch of fields to the order by clause ascending
     * Usage: $f->orderByAsc("last_name","first_name");
     * @param  array|string     $arrayOfFields An array of strings or all the fields separated by commas
     * @return DataAccessObject
     */
    public function orderByAsc($arrayOfFields)
    {
        if (func_num_args() == 1 && is_array($arrayOfFields) && count($arrayOfFields) > 0) {
            foreach ($arrayOfFields as $field) {
                $this->orderByField($field);
            }
        } else {
            $this->orderByAsc(func_get_args());
        }

        return $this;
    }

    /**
     * Limit the number of rows returned by the database
     * Usage: $f->limit(10);
     * @param  integer          $number The number of rows to return
     * @param  integer          $offset The row to start at
     * @return DataAccessObject
     */
    public function limit($number, $offset = 0)
    {
        if ((int) $offset > 0) {
            $this->setLimitClause("LIMIT " . (int) $offset . "," . (int) $number);
        } else {
            $this->setLimitClause("LIMIT " . (int) $number);
        }

        return $this;
    }

    /**
     * Set and replace the entire limit clause
     * @param  string                  $limitClause The limit clause
     * @return DataAccessObjectFactory for chaining
     */
    public function setLimitClause($limitClause)
    {
        $this->limitClause = $limitClause;

        return $this;
    }

    /**
     * Get the limit clause
     * @return string
     */
    public function getLimitClause()
    {
        return $this->limitClause;
    }

    /**
     * Wrapper to the limit function to paging to page through data
     * Usage: $f->paging(2,50);
     * @param  integer          $pageNumber  The page number the cursor is on
     * @param  integer          $rowsPerPage Number of rows per page
     * @return DataAccessObject
     */
    public function paging($pageNumber, $rowsPerPage = 20)
    {
        $this->limit($rowsPerPage, ($pageNumber - 1) * $rowsPerPage);

        return $this;
    }

    /**
     * Clear the internal bindings of a factory
     * @return $this
     */
    public function clearBindings()
    {
        $this->conditional = new AndConditional();

        return $this;
    }

    /**
     * Get count of the number of rows from the database (based on Bindings).
     * @return integer The count of rows
     */
    public function count()
    {
        if (static::getIdField()) {
            return $this->getSingleFieldFunctionValue('COUNT', static::getEscapedTableName() . '.' . static::getIdField());
        } else {
            return $this->getSingleFieldFunctionValue('COUNT', '*');
        }
    }

    /**
     * Sum a field in the rows returned (based on Bindings).
     * @return integer
     */
    public function sum($field)
    {
        return $this->getSingleFieldFunctionValue('SUM', $field);
    }

    /**
     * Truncate all the data in the table
     * @codeCoverageIgnore
     */
    public function truncateTable()
    {
        $this->update("TRUNCATE TABLE " . static::getEscapedTableName());
    }

    /**
     * Delete rows based on custom where clause
     * @param  string $whereClause
     * @return bool
     */
    public function deleteWhere($whereClause)
    {
        return $this->update("DELETE FROM " . static::getEscapedTableName() . " WHERE " . $whereClause);
    }

    /**
     * Find the first object based on the clause
     * @param  string           $clause
     * @return DataAccessObject
     */
    public function findFirst($clause = "")
    {
        $a = $this->find($clause . " LIMIT 1");
        if ($a != null) {
            return reset($a);
        } else {
            return null;
        }
    }

    /**
     * Find distinct values of a single column. Can be filtered by the clause
     * @param  string $field  The field to select
     * @param  string $clause The the clause to filter on
     * @return array
     */
    public function findDistinctField($field, $clause = "")
    {
        $array = [];

        $this->setSQL('SELECT DISTINCT(' . $field . ") as fdf FROM " . static::getEscapedTableName() . " " . $clause);

        foreach ($this->getArray() as $row) {
            $array[] = $row["fdf"];
        }

        return $array;

    }

    /**
     * Find all the values of a single column. Can be filtered by the clause
     * @param  string $field  The field to select
     * @param  string $clause The the clause to filter on
     * @return array
     */
    public function findField($field, $clause = "")
    {
        $array = [];

        $this->setSQL('SELECT ' . $field . " as ff FROM " . static::getEscapedTableName() . " " . $clause);

        foreach ($this->getArray() as $row) {
            $array[] = $row["ff"];
        }

        return $array;
    }

    /**
     * Find the first value of a single column. Can be filtered by the clause
     * @param  string $field  The field to select
     * @param  string $clause The the clause to filter on
     * @return string
     */
    public function findFirstField($field, $clause = "")
    {
        $a = $this->findField($field, $clause . " LIMIT 1");

        if ($a) {
            return reset($a);
        } else {
            return null;
        }
    }

    /**
     * Get a count of rows based on the filter clause
     * @param  string  $clause The the clause to filter on
     * @return integer the count of rows
     */
    public function getCount($clause = "")
    {
        if ($this->getIdField() != '') {
            return (int) ($this->sqlFunctionFieldQuery('COUNT', static::getEscapedTableName() . "." . static::getIdField(), $clause));
        } else {
            return (int) ($this->sqlFunctionFieldQuery('COUNT', '*', $clause));
        }
    }

    /**
     * Find the maximum value of a single column. Can be filtered by the clause
     * @param  string $field  The field to get the max value of
     * @param  string $clause The the clause to filter on
     * @return string
     */
    public function getMaxField($field, $clause = "")
    {
        return $this->sqlFunctionFieldQuery('MAX', $field, $clause);
    }

    /**
     * Find the sum of values for a single column. Can be filtered by the clause
     * @param  string $field  The field to sum
     * @param  string $clause The the clause to filter on
     * @return string
     */
    public function getSumField($field, $clause = "")
    {
        return $this->sqlFunctionFieldQuery('SUM', $field, $clause);
    }

    private function sqlFunctionFieldQuery($sqlFunction, $field, $clause)
    {
        $resultArray = $this->findField($sqlFunction . '(' . $field . ')', $clause);
        if (is_array($resultArray)) {
            return array_shift($resultArray);
        }
    }

    // deprecate old naming convetion
    public function orderByField($field, $direction = 'asc')
    {
        $this->orderBy($field, $direction);
    }

    protected function getConditionalSql()
    {
        $conditionalSQL = $this->conditional->getSql($this);

        if ($conditionalSQL != "") {
            $conditionalSQL = " WHERE " . $conditionalSQL;
        }

        return $conditionalSQL;
    }

    private function getSingleFieldFunctionValue($function, $field)
    {
        $result = $this->getResult(implode(" ", array("SELECT " . $function . "(" . $field . ") as val", $this->getFromClause(), $this->getJoinClause(), $this->getConditionalSql())));

        return $result->fetchColumn();
    }

    /**
     * @return string
     */
    private static function getEscapedTableName()
    {
        if (strpos(static::getTableName(), "-") !== false) {
            return '`' . static::getTableName() . '`';
        } else {
            return static::getTableName();
        }
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([
            'fields' => $this->fields,
            'conditional' => $this->conditional,
            'joinClause' => $this->joinClause,
            'groupByClause' => $this->groupByClause,
            'orderByClause' => $this->orderByClause,
            'limitClause' => $this->limitClause
        ]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param  string $serialized <p>
     *                            The string representation of the object.
     *                            </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->fields = $unserialized['fields'];
        $this->conditional = $unserialized['conditional'];
        $this->joinClause = $unserialized['joinClause'];
        $this->groupByClause = $unserialized['groupByClause'];
        $this->orderByClause = $unserialized['orderByClause'];
        $this->limitClause = $unserialized['limitClause'];

        // reconnect to database
        parent::__construct(static::getDatabaseName());

    }

}
