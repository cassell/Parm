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
		if(is_string($sql))
		{
			$this->sql = $sql;
		}
		else
		{
			throw new \Parm\Exception\ErrorException('StringBinding requires a string');
		}
	}

	function getSQL(\Parm\DataAccessObjectFactory $factory)
	{
		return $this->sql;
	}
}
