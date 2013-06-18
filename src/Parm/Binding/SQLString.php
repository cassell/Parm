<?php

namespace Parm\Binding;

abstract class SQLString
{
	function __construct(){ }

	abstract protected function getSQL($factory);
}

?>