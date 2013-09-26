<?php
namespace Parm\Binding\Conditional;

class OrConditional extends Conditional
{
	/**
     * Create a new OR conditional statement
     */
	function __construct()
	{
		parent::__construct();
	}

	/**
     * The separator that should be used in the SQL
	 * 
	 * @return string The separator that should be used in the SQL
     */
	function getSeparator()
	{
		return "OR";
	}
}