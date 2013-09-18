<?php

namespace Parm\Binding;

class Binding extends SQLString
{
	/**
     * @var string
     */
	public $field;
	
	/**
     * @var string
     */
	public $value;
	
	/**
     * @var string
     */
	public $operator;
	
	/**
     * Create a new Binding from the field(column_name), operatore(=,!=,etc), and value. The binding will be added to a conditional
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
	 * @return string SQL that will be added to a WHERE clause
     */
	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString($this->value) . "'";
	}

}