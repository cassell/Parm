<?php

namespace Parm\Binding;

class FalseBooleanBinding extends Binding
{
	function __construct($field)
	{
		parent::__construct($field, '=', '0');
	}
}

?>