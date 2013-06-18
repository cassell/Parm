<?php

namespace Parm\Binding;

class EqualsBinding extends Binding
{
	function __construct($field, $value)
	{
		parent::__construct($field, '=', $value);
	}
}

?>