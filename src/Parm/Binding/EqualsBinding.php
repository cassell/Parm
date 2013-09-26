<?php

namespace Parm\Binding;

class EqualsBinding extends Binding
{
	/**
     * Filter rows where the field/column = value
	 * @param $field string
	 * @param $value string
     */
	function __construct($field, $value)
	{
		parent::__construct($field, '=', $value);
	}
}
