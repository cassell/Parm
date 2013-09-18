<?php

namespace Parm\Binding;

class Binding extends SQLString
{
	public $field;
	public $value;
	public $operator;
	
	function __construct($field, $operator, $value)
	{
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
		parent::__construct();
	}

	/**
     * Return the SQL String
	 * 
	 * @return string SQL that will be added to a WHERE clause
     */
	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString($this->value) . "'";
	}

}