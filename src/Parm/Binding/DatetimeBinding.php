<?php

namespace Parm\Binding;

class DatetimeBinding extends Binding
{
	
	/**
     * Filter rows where the field/column since date passed
	 * @param string $field 
	 * @param string|\DateTime|int $mixed 
     */
	function __construct($field, $operator, $mixed)
	{
		parent::__construct($field, $operator,$mixed);
	}
	
	function getSQL(\Parm\DataAccessObjectFactory $factory)
	{
		if($this->value === null)
		{
			return $factory->escapeString($this->field) . " " . $this->operator . " NULL";
		}
		else if($this->value instanceof \DateTime)
		{
			return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString(($this->value->format($factory->databaseNode->getDatetimeStorageFormat()))) . "'";
		}
		else if(is_numeric($this->value))
		{
			$date = new \DateTime();
			$date->setTimestamp((int)$this->value);
			return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString($date->format($factory->databaseNode->getDatetimeStorageFormat())) . "'";
		}
		else
		{
			return $factory->escapeString($this->field) . " " . $this->operator . " '" . $factory->escapeString((string)$this->value) . "'";
		}
	}
	
}