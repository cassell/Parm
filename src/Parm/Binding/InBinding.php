<?php

namespace Parm\Binding;

class InBinding extends SQLString
{
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
			return $factory->escapeString($this->field) . " = " . reset($this->array);
		}
		else if(count($this->array) > 0)
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
			throw new Exception("The array passed to the InBinding is empty");
		}
	}

}

?>