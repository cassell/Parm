<?php

namespace Parm\Binding;

class StringBinding extends SQLString
{
	/**
	 * The sql to filter on
     * @var string
     */
	public $sql;
	
	/**
     * Filter rows on a string
	 * @param $field string
	 * @param $value string
     */
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
