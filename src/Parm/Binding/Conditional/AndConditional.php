<?php
namespace Parm\Binding\Conditional;

class AndConditional extends Conditional
{
	function __construct()
	{
		parent::__construct();
	}

	function getSeparator()
	{
		return "AND";
	}
}