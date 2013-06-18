<?php

namespace Parm\Binding;

class NotEqualsBinding extends Binding
{

	function __construct($field, $value)
	{
		parent::__construct($field, '!=', $value);
	}

}

?>
