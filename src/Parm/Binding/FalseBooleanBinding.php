<?php

namespace Parm\Binding;

class FalseBooleanBinding extends Binding
{
	/**
     * Filter rows where the field/column is false
	 * @param $field string
     */
	function __construct($field)
	{
		parent::__construct($field, '=', '0');
	}
}
