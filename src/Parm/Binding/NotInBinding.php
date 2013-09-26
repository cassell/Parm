<?php

namespace Parm\Binding;

class NotInBinding extends SQLString
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
     * Filter rows by an array of values but exclude the values in the array
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
			return $factory->escapeString($this->field) . " != " . $factory->escapeString(reset($this->array));
		}
		else if(count($this->array > 1))
		{
			foreach($this->array as $key => $item)
			{
				$this->array[$key] = $factory->escapeString($item);
			}

			return $factory->escapeString($this->field) . " NOT IN (" . implode(",", $this->array) . ")";
		}
		else
		{
			throw new \Exception("The array passed to the NotInBinding is empty");
		}
	}

}
