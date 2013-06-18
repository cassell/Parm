<?php

namespace Parm\Conditional;

class OrConditional extends Conditional
{
	function __construct()
	{
		parent::__construct();
	}

	function getSeparator()
	{
		return "OR";
	}
}

?>