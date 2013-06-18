<?php

namespace Parm\Binding;

class StringBinding extends SQLString
{
	function __construct($sql)
	{
		$this->sql = $sql;
		parent::__construct();
	}

	function getSQL($factory)
	{
		return $this->sql;
	}
}

?>