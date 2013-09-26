<?php

namespace Parm\Binding;

class ContainsBinding extends SQLString
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
	public $query;
	
	/**
     * Filter rows where the field/column contains the query string
	 * @param $field string
	 * @param $query string
     */
	function __construct($field, $query)
	{
		parent::__construct();

		$this->field = $field;
		$this->query = $query;
	}

	/**
     * Return the SQL String
	 * 
	 * @param $factory DataAccessObjectFactory
	 * @return string
     */
	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " LIKE '%" . $factory->escapeString(str_replace("_", "\_", str_replace("%", "\%", $this->query))) . "%'";
	}
}
