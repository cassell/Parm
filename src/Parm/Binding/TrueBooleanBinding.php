<?php

namespace Parm\Binding;

class TrueBooleanBinding extends Binding
{
	function __construct($field)
	{
		parent::__construct($field, '=', '1');
	}
}

?>