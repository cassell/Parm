<?php
namespace Parm\Binding\Conditional;

class OrConditional extends Conditional
{
	/**
     * Createa a new conditional that will joing bindings with OR
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