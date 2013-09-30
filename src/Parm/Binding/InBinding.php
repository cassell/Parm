<?php

namespace Parm\Binding;

class InBinding extends SQLString
{
	
	/**
	 * The field/column to filter on
     * @var string
     */
	public $field;
	
	/**
	 * The array to filter the field/column on
     * @var array
     */
	public $array;
	
	/**
     * Filter rows by an array of values
	 * @param $field The field or column to filter on
	 * @param $array The array of values to filter the field or column on
     */
	function __construct($field, $array)
	{
		parent::__construct();
		$this->field = $field;
		$this->array = $array;
	}

	function getSQL($factory)
	{
		if(count($this->array) == 1)
		{
			return $factory->escapeString($this->field) . " = " . $factory->escapeString(reset($this->array));
		}
		else if(count($this->array) > 1)
		{
			foreach($this->array as $key => $item)
			{
				if(is_numeric($item))
				{
					$this->array[$key] = (int) $item;
				}
				else
				{
					$this->array[$key] = "'" . $factory->escapeString($item) . "'";
				}
			}

			return $factory->escapeString($this->field) . " IN (" . implode(",", $this->array) . ")";
		}
		else
		{
			throw new \Parm\Exception\ErrorException("The array passed to the InBinding is empty");
		}
	}

}