<?php

namespace Parm\Binding;

class Binding extends SQLString
{
	/**
	 * The field/column to filter on
     * @var string
     */
	public $field;
	
	/**
	 * The value to filter the field/column on
     * @var string
     */
	public $value;
	
	/**
	 * The operator for the filtering
     * @var string
     */
	public $operator;
	
	/**
     * Create a new Binding from the field(column_name), operator(=,!=,etc), and value.
	 * @param $field string
	 * @param $operator string
	 * @param $value string
     */
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
	 * @param $factory DataAccessObjectFactory
	 * @return string
     */
	function getSQL($factory)
	{
		if($this->value == null)
		{
			return $factory->escapeString($this->field) . " " . $this->operator . " NULL";
		}
		else
		{
			return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString($this->value) . "'";
		}
		
	}
	
}