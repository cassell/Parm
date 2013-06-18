<?php

namespace Parm\Binding;

class NotInBinding extends SQLString
{

	function __construct($field, $array)
	{
		parent::__construct();
		$this->field = $field;
		$this->array = $array;
	}

	function getSQL($factory)
	{
		if(count($this->array > 0))
		{
			foreach($this->array as $key => $item)
			{
				$this->array[$key] = $factory->escapeString($item);
			}

			return $factory->escapeString($this->field) . " NOT IN (" . implode(",", $this->array) . ")";
		}
		else
		{
			throw new SQLiciousErrorException("The array passed to the NotInBinding is empty");
		}
	}

}

?>