<?php

namespace Parm\Binding;

class CaseSensitiveEqualsBinding extends EqualsBinding
{
	function __construct($field, $value)
	{
		parent::__construct($field, $value);
	}

	function getSQL($factory)
	{
		return $factory->escapeString($this->field) . " COLLATE " . $factory->databaseNode->serverCaseSensitiveCollation . " LIKE '" . $factory->escapeString(str_replace("_", "\_", str_replace("%", "\%", $this->value))) . "'";
	}
}

?>