<?php

namespace Parm\Binding;

class ContainsBinding extends SQLString
{
	function __construct($field, $query)
	{
		parent::__construct();

		$this->field = $field;
		$this->query = $query;
	}

	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " LIKE '%" . $factory->escapeString(str_replace("_", "\_", str_replace("%", "\%", $this->query))) . "%'";
	}
}

?>