<?php

namespace Parm\Binding;

class Binding extends SQLString
{
	function __construct($field, $operator, $value)
	{
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
		parent::__construct();
	}

	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString($this->value) . "'";
	}

}

?>