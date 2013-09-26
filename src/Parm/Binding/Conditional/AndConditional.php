<?php
namespace Parm\Binding\Conditional;

class AndConditional extends Conditional
{
	/**
     * Create a new conditional that will joing bindings with AND
     */
	public function __construct()
	{
		parent::__construct();
	}

	/**
     * The separator that should be used in the SQL
	 * 
	 * @return string The separator that should be used in the SQL
     */
	public function getSeparator()
	{
		return "AND";
	}
}